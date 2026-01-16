<?php

use Carbon\Carbon;
use Services\Logs\FileLoggerService;

abstract class Lotto_Lotteries_Keno extends LottoLotteriesLotteryLcs
{
    use Lotto_Hasscraping;

    public const DRAW_DATE_FIX_DELAY_MINUTES = 60;
    public const ERROR_LOG_DELAY_IN_HOURS = 6;
    public const IS_DRAW_DATE_FIX_ENABLED = false;

    protected string $lottery_slug;

    public function get_results(): void
    {
        $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($nextDrawDate->isFuture()) {
            return;
        }

        try {
            $data = $this->getResultsFromLcs($this->lottery_slug);
            $numbers = $this->parseResultsFromLcs($data, $nextDrawDate);
            $this->insert_draw_numbers($numbers, $nextDrawDate);
            return;
        } catch (\Throwable $e) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Lcs Sources',
                exception: $e,
                nextDrawDateFormatted: $nextDrawDate->format('YmdHi'),
                delayInHours: static::ERROR_LOG_DELAY_IN_HOURS,
            );
            
            echo 'Error in all sources.';
        }
    }

    public function parseResultsFromLcs(array $dataFromLcs, Carbon $nextDrawDate): array
    {
        $drawIndex = -1;
        foreach($dataFromLcs as $key => $datum) {
            if ($datum['timestamp'] === $nextDrawDate->timestamp) {
                $drawIndex = $key;
                break;
            }
        }

        if ($drawIndex === -1) {
            if (static::IS_DRAW_DATE_FIX_ENABLED) {
                $this->drawDateFix($nextDrawDate);
            }
            throw new Exception($this->lottery_slug . ' - [LCS] source unable to find draw');
        }

        return $dataFromLcs[$drawIndex]['numbers'][0];
    }

    public function insert_draw_numbers(array $numbers, Carbon $providerNextDrawDate): void
    {
        // we dont need to fetch additionally draw date - we checked above that providerNextDrawDate is valid
        $this->validateResults([static::LOTTERY_JACKPOT, $providerNextDrawDate->format('Y-m-d H:i'), $numbers, [], []], static::LOTTERY_NUMBERS_COUNT, 0, static::LOTTERY_NUMBERS_RANGE, [], 0);

        $drawDateTime = $providerNextDrawDate->setTimezone($this->lottery['timezone']);
        if ($this->shouldUpdateLottery($drawDateTime, static::LOTTERY_JACKPOT)) { // TODO: {Vordis 2021-11-17 11:38:30} should be separate (jackpot update and draw insertion logic), but for that we need to rebuilt set_lottery_with_data
            $this->set_lottery_with_data($this->lottery, static::LOTTERY_JACKPOT, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbers, [], [], $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }

    public function drawDateFix(Carbon $providerNextDrawDate): void
    {
        try {
            if (Carbon::now()->diffInMinutes($providerNextDrawDate) < static::DRAW_DATE_FIX_DELAY_MINUTES) {
                return;
            }

            $drawDateTime = $providerNextDrawDate->setTimezone($this->lottery['timezone']);
            $nextDrawDateTime = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $drawDateTime, 1);
            $nextDrawDateTimeUtc = $nextDrawDateTime->clone()->setTimezone(new DateTimeZone('UTC'));

            $drawDateTimeFormatted = $drawDateTime->format(Helpers_Time::DATETIME_FORMAT);
            $nextDrawDateTimeFormatted = $nextDrawDateTime->format(Helpers_Time::DATETIME_FORMAT);
            $nextDrawDateTimeUtcFormatted = $nextDrawDateTimeUtc->format(Helpers_Time::DATETIME_FORMAT);

            // Process tickets
            DB::update('whitelabel_user_ticket')
                ->value('valid_to_draw', $nextDrawDateTimeFormatted)
                ->value('draw_date', $nextDrawDateTimeFormatted)
                ->where('lottery_id', '=', $this->lottery['id'])
                ->where('valid_to_draw', '=', $drawDateTimeFormatted)
                ->where('draw_date', '=', $drawDateTimeFormatted)
                ->where('status', '=', 0)
                ->execute();

            $ticketCount = DB::count_last_query();

            // Update lottery next draw date
            $this->lottery_to_update = Model_Lottery::find_by_pk($this->lottery['id']);
            $this->lottery_to_update->set([
                'next_date_local' => $nextDrawDateTimeFormatted,
                'next_date_utc' => $nextDrawDateTimeUtcFormatted,
            ]);
            $this->lottery_to_update->save();

            // Log
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->info("{$this->lottery['slug']} draw date fix has been activated. Total of {$ticketCount} tickets moved from {$drawDateTimeFormatted} to {$nextDrawDateTimeFormatted}");

        } catch (\Throwable $e) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error("{$this->lottery['slug']} draw date fix failed! Total of {$ticketCount} tickets from {$drawDateTimeFormatted} were NOT moved to next draw at {$nextDrawDateTimeFormatted}! {$e->getMessage()}");

            echo $e->__toString();
        }
    }
}
