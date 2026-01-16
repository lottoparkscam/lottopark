<?php

use Fuel\Core\Config;
use Fuel\Core\DB;
use Fuel\Core\Package;
use Services\Logs\FileLoggerService;

abstract class Lotto_Lotteries_GGFeed extends Lotto_Lotteries_Feed
{
    const OTOSLOTTO = 24;
    const HATOSLOTTO = 25;
    const SKANDINAV_LOTTO = 31;
    const LOTTO_MULTI_MULTI = 32;

    /**
     *
     * @var array
     */
    protected $download_draw_hour_delay_limit = 8;

    /**
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * @var array
     */
    protected $json_data;

    /**
     * @param array $lottery
     * @param int   $modifier
     *
     * @return void
     */
    abstract public function fetch_draw_data(array $lottery, int $modifier = 0): void;

    /**
     * @return void
     * @throws Exception
     */
    public function update_lottery(): void
    {
        $upd = Model_Lottery::find_by_pk($this->lottery['id']);

        $dt = new DateTime("now", new DateTimeZone("UTC"));

        if ($this->jackpot == 0) {
            $this->jackpot = false;
        }

        //
        $set = $this->prepare_lottery_values($dt, $upd->current_jackpot);
        $upd->set($set);

        if ($upd->next_date_local == null || $upd->next_date_local == $upd->last_date_local) {
            $act_local_date = Lotto_Helper::get_lottery_next_draw($upd);

            $dbdate = $act_local_date->format(Helpers_Time::DATETIME_FORMAT);
            $act_local_date->setTimezone(new DateTimeZone("UTC"));
            $upd->set(
                [
                    'next_date_local' => $dbdate,
                    'next_date_utc' => $act_local_date->format(Helpers_Time::DATETIME_FORMAT)
                ]
            );
            Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
        } else {
            $this->check_dates($dt);
        }

        $upd->save();

        // TODO: testing duplicated emails' possible solution
        Lotto_Helper::clear_cache(['model_lottery_draw', 'model_lottery_prize_data']);
        Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
    }

    /**
     * @param DateTime $dt
     * @param mixed    $current_jackpot
     *
     * @return array
     * @throws Exception
     */
    public function prepare_lottery_values(DateTime $dt, $current_jackpot): array
    {
        // TODO: Still could use some refactoring

        $set = [];

        $dt_lt = clone $dt;
        $dt_lt->setTimezone(new DateTimeZone($this->lottery['timezone']));

        //
        $last_date_local = null;
        $next_date_local = null;

        if ($this->lottery['last_date_local'] != null) {
            $last_date_local = new DateTime(
                $this->lottery['last_date_local'],
                new DateTimeZone($this->lottery['timezone'])
            );
        }

        if ($this->lottery['next_date_local'] != null) {
            $next_date_local = new DateTime(
                $this->lottery['next_date_local'],
                new DateTimeZone($this->lottery['timezone'])
            );
        }

        //
        $set['last_update'] = $dt->format(Helpers_Time::DATETIME_FORMAT);

        // If date is empty, we would not like to continue script processing
        if (empty($this->date) || !$this->date->format(Helpers_Time::DATETIME_FORMAT)) {
            throw new Exception('Received empty date key.');
        }


        if (sprintf("%3.6f", $this->jackpot) == $this->lottery['current_jackpot'] &&
            $last_date_local !== null && $this->date <= $last_date_local
        ) {
            return $set;
        }

        $draw = null;
        if ($this->jackpot !== null && $this->jackpot !== false &&
            ($this->lottery['draw_jackpot_set'] == 0 || $this->overwrite_jackpot)
        ) {
            $set['current_jackpot'] = $this->jackpot;
            $set['current_jackpot_usd'] = Helpers_Currency::convert_to_USD(
                $this->jackpot,
                $this->lottery['currency'],
                8,
                8
            );
            $set['draw_jackpot_set'] = 1;
        } else {
            // I have moved that checking over here to make some clearance
            $next_date_local_checked = false;
            if ($next_date_local !== null && $dt_lt !== null &&
                ($this->date->format(Helpers_Time::DATETIME_FORMAT) == $next_date_local->format(Helpers_Time::DATETIME_FORMAT) ||

                    $dt_lt >= $next_date_local->sub(new DateInterval("PT2H")))
            ) {
                $next_date_local_checked = true;
            }
            // sometimes lotteries update their jackpots (SIC!!!)
            // do not update 2h before the draw (that should fix UK Lottery)
            // other various checks for different lotteries, it's quite complicated

            if (!($last_date_local === null ||
                    $this->date > $last_date_local ||
                    $next_date_local_checked) &&
                $this->jackpot !== null && $this->jackpot !== false &&
                $this->lottery['draw_jackpot_set'] == 1 &&
                sprintf("%3.6f", $this->jackpot) != $this->lottery['current_jackpot']
            ) {
                $set['current_jackpot'] = $this->jackpot;
                $set['current_jackpot_usd'] = Helpers_Currency::convert_to_USD(
                    $this->jackpot,
                    $this->lottery['currency'],
                    8,
                    8
                );

                $debug = [
                    $this->lottery,
                    $set,
                    $last_date_local->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
                    $this->date->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
                    $next_date_local->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
                    $this->jackpot
                ];

                // Email
                $title = "Lotto Emergency: Jackpot changed: " . $this->lottery['name'];
                $jackpot_text = sprintf("%3.6f", $this->jackpot);
                $body = "The jackpot has changed! More info: " .
                    "new jackpot: " . $jackpot_text . " " .
                    "current jackpot: " . $this->lottery['current_jackpot'] . " " .
                    var_export($debug, true) .
                    " [helper.php/processLottery]";
                $this->send_emergency_email($title, $body);
            }
        }

        if (($last_date_local === null || $this->date > $last_date_local) &&
            $this->validate_draw_date($this->lottery, $this->date) === true) {
            try {
                DB::start_transaction();

                // make jackpot pending
                if ($this->jackpot === false || $this->jackpot === null) {
                    $set['current_jackpot'] = null;
                    $set['current_jackpot_usd'] = null;
                }

                if (in_array($this->lottery['id'], $this->lottery_noestimated_ids)) {
                    $set['estimated_updated'] = 0;
                }

                $set['last_date_local'] = $this->date->format(Helpers_Time::DATETIME_FORMAT);

                asort($this->numbers);
                if ($this->bonus_numbers) {
                    asort($this->bonus_numbers);
                }
                $set['last_numbers'] = implode(',', $this->numbers);
                $set['last_bnumbers'] = !empty($this->bonus_numbers) ? implode(',', $this->bonus_numbers) : null;

                //
                $this->insert_draw($dt, $current_jackpot, $set['last_numbers'], $set['last_bnumbers']);


                $set['additional_data'] = serialize($this->additional_data);

                if ($this->jackpot !== null && $this->jackpot !== false) {
                    $set['current_jackpot'] = $this->jackpot;
                    $set['current_jackpot_usd'] = Helpers_Currency::convert_to_USD(
                        $this->jackpot,
                        $this->lottery['currency'],
                        8,
                        8
                    );
                }

                // update multi-draw current_draw and is finished flags
                Helpers_Multidraw::update_current_draw_date($this->lottery, $last_date_local, $this->date);
                Helpers_Multidraw::mark_as_finished($this->lottery, $this->date);

                DB::commit_transaction();

            } catch (Exception $e) {
                DB::rollback_transaction();
                throw $e;
            }
        }

        return $set;
    }

    /**
     * @param mixed       $dt
     * @param             $jackpot
     * @param string|null $last_numbers
     * @param string|null $last_bnumbers
     *
     * @return void
     * @throws Exception
     */
    public function insert_draw(DateTime $dt, $jackpot, ?string $last_numbers, ?string $last_bnumbers): void
    {
        $type = Model_Lottery_Type::get_lottery_type_for_date($this->lottery, $this->date->format(Helpers_Time::DATE_FORMAT));  // DATETIME?
        if ($type === null) {
            throw new Exception('Helper - No lottery type.');
        }

        $draw = Model_Lottery_Draw::forge();
        $draw->set([
            'lottery_id' => $this->lottery['id'],
            'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
            'date_local' => $this->date->format(Helpers_Time::DATETIME_FORMAT),
            'jackpot' => $jackpot,
            'numbers' => $last_numbers,
            'bnumbers' => $last_bnumbers,
            'lottery_type_id' => $type['id'],
            'total_prize' => 0,
            'total_winners' => 0,
            'final_jackpot' => 0,
            'additional_data' => serialize($this->additional_data)
        ]);
        $draw->save();

        // Set draw id to draw email notifications list
        $notification_draw_helper = new Helpers_Notifications_Draw();
        $notification_draw_helper->update_draw_notification_emails($draw['lottery_id'], $draw['date_local'], $draw['id']);
    }

    /**
     * @param DateTime $dt
     *
     * @return void
     * @throws Exception
     */
    public function check_dates(DateTime $dt): void
    {
        $download_draw_hour_delay_limit = $this->download_draw_hour_delay_limit;

        $last_date_local = null;
        $next_date_local = null;

        if ($this->lottery['last_date_local'] != null) {
            $last_date_local = new DateTime(
                $this->lottery['last_date_local'],
                new DateTimeZone($this->lottery['timezone'])
            );
        }

        if ($this->lottery['next_date_local'] != null) {
            $next_date_local = new DateTime(
                $this->lottery['next_date_local'],
                new DateTimeZone($this->lottery['timezone'])
            );
        }

        $nl_diff = 0;
        if (isset($next_date_local)) {
            $nl_diff = intval(Lotto_View::date_diff($next_date_local, $dt, "%r%h"));
        }

        if ($nl_diff == $download_draw_hour_delay_limit) {
            $title = "Lotto Emergency: Lottery Draw Date Mismatch";
            $body = "[important] It seems that draw date passed {$download_draw_hour_delay_limit}h ago for " .
                $this->lottery['name'] . "! It may mean that there is draw date " .
                "mismatch! This message will stop sending automatically after 1h. " .
                "[helper.php/processLottery].";
            $this->send_emergency_email($title, $body);
        } elseif ($next_date_local < $last_date_local) {
            $title = "Lotto Emergency: Lottery Draw Date Mismatch";
            $body = "[important] It seems we missed the draw date change " .
                "for lottery " . $this->lottery['name'] .
                ". Please fix it ASAP! [helper.php/processLottery].";
            $this->send_emergency_email($title, $body);
        }
    }
    
    /**
     * @param string $title
     * @param string $body
     * @param array  $recipients
     *
     * @return void
     */
    protected function send_emergency_email($title, $body, $recipients = []): void
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
     * @param string $url
     *
     * @return void
     * @throws Exception
     */
    public function init_json_feed($url): void
    {
        $json = Lotto_Helper::load_HTML_url($url);

        $this->json_data = json_decode($json, true);
    }

    /**
     * @param string $url
     *
     * @return void
     * @throws Exception
     */
    public function init_xml_feed($url): void
    {
        $doc = new DOMDocument();
        $doc->loadXML(Lotto_Helper::load_HTML_url($url));

        $this->xpath = new DOMXPath($doc);
    }

    /**
     * @param string $url
     *
     * @return void
     * @throws Exception
     */
    public function init_html_feed($url): void
    {
        $htmlContent = Lotto_Helper::load_HTML_url($url);

        if (!empty($htmlContent)) {
            $doc = new DOMDocument();
            $doc->loadHTML($htmlContent);

            $this->xpath = new DOMXPath($doc);
        }
    }

    /**
     * @param string $tag
     * @param string $id
     *
     * @return DOMNodeList
     */
    public function get_element_by_id($tag, $id): DOMNodeList
    {
        return $this->xpath->query("//{$tag}[contains(@id, '{$id}')]");
    }

    /**
     * @param string $tag
     * @param        $classname
     *
     * @return DOMNodeList
     */
    public function get_elements_by_classname($tag, $classname): DOMNodeList
    {
        return $this->xpath->query("//{$tag}[contains(@class, '{$classname}')]");
    }

    /**
     * @param $query
     *
     * @return DOMNodeList
     */
    public function get_elements_by_query($query): DOMNodeList
    {
        return $this->xpath->query($query);
    }

    /**
     * Sends a delayed error log.
     *
     * @param string $slug The lottery slug.
     * @param string $error The short error name.
     * @param string $errorMessage The full error message to be logged.
     * @param int $delayInHours The delay time for sending the error log, in hours. Defaults to 5 hours.
     *
     * @return void
     */
    public function sendDelayedErrorLog(string $slug, string $error, string $errorMessage, int $delayInHours = 4): void
    {
        $cacheKey = str_replace([' ', '-', '_'], '', $slug . $error . 'ErrorAttempt');
        $fileLoggerService = Container::get(FileLoggerService::class);

        $fileLoggerService->shouldSendLogWhenProblemExistsAfterGivenTime(
            $delayInHours,
            "[$slug] $error $errorMessage Failed. Intervention required.",
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }
}
