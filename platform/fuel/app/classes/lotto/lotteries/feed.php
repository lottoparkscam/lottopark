<?php

use Carbon\Carbon;
use Fuel\Core\Config;
use Fuel\Core\Package;
use Services\Logs\FileLoggerService;

/**
 * Lottery data feed base class
 */
abstract class Lotto_Lotteries_Feed
{
    /**
     * @var array
     */
    protected $lottery;

    /**
     *
     * @var mixed
     */
    protected $jackpot;

    /**
     *
     * @var Carbon
     */
    protected $date;

    /**
     *
     * @var Carbon
     */
    protected $date_utc;

    /**
     *
     * @var array
     */
    protected $numbers;

    /**
     *
     * @var array
     */
    protected $bonus_numbers;

    /**
     *
     * @var array
     */
    protected $prizes;

    /**
     *
     * @var bool
     */
    protected $overwrite_jackpot = false;

    /**
     *
     * @var bool
     */
    protected $should_prizes_be_overwritten = false;

    /**
     *
     * @var array
     */
    protected $additional_data = null;

    /**
     * @var array
     */
    protected $lottery_noestimated_ids = [];

    /**
     * @var string
     */
    protected string $lottery_slug = '';

    protected static $lottery_has_many_draws_a_day = false;

    protected bool $isDrawDateDue = false;

    public static function make(): self
    {
        $instance = new static;
        $slug = $instance->get_lottery_slug();
        if (!empty($slug)) {
            $instance->set_lottery($slug);
        }

        return $instance;
    }

    public function set_lottery_with_data(
        array $lottery,
        float $jackpot,
        Carbon $date,
        Carbon $date_utc,
        array $numbers,
        ?array $bonus_numbers,
        array $prizes = [],
        bool $overwrite_jackpot = false,
        bool $overwrite_prizes = false,
        array $additional_data = null
    ): void
    {
        $this->lottery = $lottery;
        $this->jackpot = $jackpot;
        $this->date = $date;
        $this->date_utc = $date_utc;
        $this->numbers = $numbers;
        $this->bonus_numbers = $bonus_numbers;
        $this->prizes = $prizes;
        $this->overwrite_jackpot = $overwrite_jackpot;
        $this->should_prizes_be_overwritten = $overwrite_prizes;
        $this->additional_data = $additional_data;
    }

    /**
     * @param Model_Lottery|array|string $lottery Lottery data or slug
     */
    public function set_lottery($lottery): void
    {
        if (is_string($lottery)) {
            $lottery = Model_Lottery::find_one_by_slug($lottery);
        }
        if ($lottery !== null && $lottery['is_enabled'] === false) {
            return;
        }
        $this->lottery = $lottery;
    }

    public function get_lottery_slug(): string
    {
        return $this->lottery_slug;
    }

    abstract public function get_results(): void;

    protected function get_draw_hour_from_draw_dates(string $shortEnglishWeekDay, bool $sendEmail = false): string
    {
        if (self::$lottery_has_many_draws_a_day) {
            throw new Exception("This lottery has many draws a day");
        }
        try {
            $drawDates = json_decode($this->lottery['draw_dates'], true);
            $firstDrawHour = null;

            foreach ($drawDates as $drawDate) {
                $drawWeekDay = substr($drawDate, 0, 3);
                $drawHour = substr($drawDate, 4);

                if ($drawWeekDay === $shortEnglishWeekDay) {
                    $firstDrawHour = $drawHour;
                    break;
                }
            }

            if (empty($firstDrawHour)) {
                $firstDrawHour = substr($drawDates[0], 4);
            }

            if (empty($firstDrawHour) && $sendEmail) {
                $logTitle = '[WARNING] Lottery:update_draw_data';
                $logMessage = sprintf('Draw time guessed for unscheduled draw. Lottery: %s, weekday: %s, guessed time %s',
                    $this->lottery['slug'],
                    $shortEnglishWeekDay,
                    $firstDrawHour
                );
                Helpers_Mail::send_emergency_email($logTitle, $logMessage);
            }

        } catch (Throwable $e) {
            throw new Exception("Could not parse draw hour");
        }


        return $firstDrawHour;
    }

    /**
     * @param string $title
     * @param string $body
     * @param array  $recipients
     *
     * @return void
     */
    protected static function email_emergency($title, $body, $recipients = []): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($recipients)) {
            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
        }

        Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to($recipients);
        $email->subject($title);
        $email->body($body);
        try {
            $email->send();
        } catch (Exception $e) {
            $error_message = "There is a problem with delivering the mail. " .
                "Description of error: " . $e->getMessage();
            $fileLoggerService->error(
                $error_message
            );
        }

    }

    /**
     * Check if given date is matching our next draw date
     *
     * @param array           $lottery
     * @param Carbon $date
     *
     * @return bool
     */
    protected function validate_draw_date(array $lottery, Carbon $date): bool
    {
        // If next_date_local is empty, we should download a nearest draw (for example, for installing a new whitelotto instance)
        if (empty($lottery['next_date_local'])) {
            return true;
        }

        // Compare dates, if there is a mismatch, we shouldn't download this draw, cause it can be some special draw
        if ($lottery['next_date_local'] != $date->format(Helpers_Time::DATETIME_FORMAT)) {
            return false;
        }

        return true;
    }

    protected function checkIfJackpotIsOutdated(?string $jackpot): bool
    {
        return sprintf("%3.8f", $this->lottery['current_jackpot']) !== sprintf("%3.8f", $jackpot);
    }

    protected function checkIfFoundDrawIsNotDue(Carbon $drawDate): bool
    {
        if ($this->lottery['next_date_local'] === null) {
            return false; // we need to update if next_date is null
        }

        return Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->greaterThan($drawDate);
    }

    /**
     * @return string|null $drawDate null if draw date is not due
     */
    protected function calculateNextDueDrawDatetime(string $drawDateFeed, ?string $jackpot): ?Carbon
    {
        $drawDatetime = Carbon::parse($drawDateFeed, $this->lottery['timezone']);

		foreach (json_decode($this->lottery['draw_dates'], true) as $draw_date) { // NOTE: I omit checks with premeditation let false and null explode
			$isTheSameAsDrawDay = strpos($draw_date, $drawDatetime->shortDayName) !== false;
			if ($isTheSameAsDrawDay) {
				$drawTime = explode(' ', $draw_date)[1];
				break;
			}
		}

		$isSuperena = (int)$this->lottery['id'] === Helpers_Lottery::SUPER_ENALOTTO_ID;
		if ($isSuperena && empty($drawTime)) {
			$drawTime = explode(' ', $this->lottery['next_date_local'])[1];
		}

        if (!isset($drawTime)) {
            throw new \Exception(static::class . ' cannot find it\'s draw date, make sure it has valid draw_dates in database!');
        }
        $drawDatetime->setTimeFromTimeString($drawTime);
        
        $isDrawDateNotDue = $this->checkIfFoundDrawIsNotDue($drawDatetime);
        $shouldUpdateJackpot = $this->checkIfJackpotIsOutdated($jackpot) || $this->lottery['draw_jackpot_set'] === '0';
        $this->overwrite_jackpot = $shouldUpdateJackpot;
        if ($isDrawDateNotDue && !$shouldUpdateJackpot) {
            $drawDatetime = null; // we disable only if jackpot is not outdated. TODO: logic fallacy
        }
        
        $this->isDrawDateDue = !$isDrawDateNotDue;
        
        return $drawDatetime;
    }

}