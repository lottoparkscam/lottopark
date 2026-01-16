<?php

use Carbon\Carbon;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Models\Lottery;
use Services\CacheService;
use Services\Logs\FileLoggerService;
use Services\Lottery\Draw\UnscheduledDrawService;
use Services\PageCacheService;
use Wrappers\Event as EventWrapper;

abstract class Lotto_Lotteries_Lottery extends Lotto_Lotteries_Feed
{
    /**
     * sometimes lotteries update their jackpots (SIC!!!)
     * do not update 2h before the draw (that should fix UK Lottery)
     */
    const JACKPOT_UPDATE_MARGIN = 2;

    protected bool $draw_has_been_changed = false;
    protected ?Carbon $previous_next_draw_date = null;

    /**
     * @var int
     */
    protected $download_draw_hour_delay_limit = 8;

    /**
     * Probably it should be array of slug-s but
     * I am not quite sure if it is possible to check
     * by slug in the place that is need within that file
     * So I left that as list of id-s of lotteries
     *
     * @var array
     */
    protected $lottery_noestimated_ids = [
        3,  // eurojackpot
        7,  // lottopl
        10, // oz lotto
        11, // powerball au
        12, // saturday lotto
        13  // mond&wed lotto
    ];
    /**
     * @var Carbon
     */
    protected $now_utc_timezone;
    /**
     * @var Carbon
     */
    protected $now_lottery_timezone;
    /**
     * @var Model_Lottery
     */
    protected $lottery_to_update;
    /**
     * @var Carbon
     */
    protected $last_date_local;
    /**
     * @var Carbon
     */
    protected $next_date_local;

    protected UnscheduledDrawService $unscheduledDrawService;
    private EventWrapper $event;

    public function __construct()
    {
        $this->unscheduledDrawService = Container::get(UnscheduledDrawService::class);
        $this->event = Container::get(EventWrapper::class);
    }

    /**
     *
     * @param array    $lottery
     * @param mixed    $jackpot
     * @param Carbon $date
     * @param Carbon $date_utc
     * @param array    $numbers
     * @param array    $bonus_numbers
     * @param array    $prizes
     * @param bool     $overwrite_jackpot
     * @param bool     $overwrite_prizes
     * @param array    $additional_data
     *
     * @throws Exception
     */
    public function set_lottery_with_data(
        $lottery,
        $jackpot,
        $date,
        $date_utc,
        $numbers,
        $bonus_numbers,
        $prizes = [],
        $overwrite_jackpot = false,
        $overwrite_prizes = false,
        $additional_data = null
    ): void {
        parent::set_lottery_with_data(
            $lottery,
            $jackpot,
            $date,
            $date_utc,
            $numbers,
            $bonus_numbers,
            $prizes,
            $overwrite_jackpot,
            $overwrite_prizes,
            $additional_data
        );
        $this->now_utc_timezone = Carbon::now(new DateTimeZone("UTC"));
        $this->now_lottery_timezone = Carbon::now(new DateTimeZone($this->lottery['timezone']));
        $this->lottery_to_update = Model_Lottery::find_by_pk($this->lottery['id']);
        $this->process_lottery();
    }

    public function check_match(int $match_n, int $match_b, int $match_others): bool
    {
        return $match_n !== 0 || $match_b !== 0 || $match_others === 1;
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool {
        return ($type['bextra'] == 0 && $wintype->match_n == $match_n && $wintype->match_b == $match_b)
            || ($type['bextra'] == 1 && $wintype->match_n == $match_n &&
                ($wintype->match_b == 0 || ($wintype->match_b != 0 && $wintype->match_b == $match_b)));
    }

    public function isKenoTypeDataWinning(
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        array $ticketKenoData
    ): bool
    {
        $isMatchedNumbersCountWinning = $wintype->match_b == $match_n;
        $isSelectedNumbersCountWinning = $wintype->match_n == $ticketKenoData['numbers_per_line'];

        return $isMatchedNumbersCountWinning && $isSelectedNumbersCountWinning;
    }

    /**
     *
     * @param int   $match_n
     * @param array $line_numbers
     *
     * @return int
     */
    public function match_n($match_n, $line_numbers): int
    {
        foreach ($this->numbers as $number) {
            if (in_array($number, $line_numbers)) {
                $match_n++;
            }
        }

        return $match_n;
    }

    /**
     * When bextra is non-zero, the provider draws extra numbers (user cannot select these).
     * match_b++ means that we are summing the bonus tier e.g. match 5+2 tier.
     * If provider draws more than 1 bonus number AND tiers only have X+1 format then we need to always return 1.
     * Check platform/fuel/app/classes/lotto/lotteries/ozlotto.php for an example.
     *
     * When bextra is zero, the user can select bonus numbers and tier is usually calculated normally e.g. 4+2, 4+1, 4+0.
     *
     * @param int   $match_b
     * @param array $type
     * @param array $line_bnumbers
     * @param array $line_numbers
     *
     * @return int
     */
    public function match_b($match_b, $type, $line_bnumbers, $line_numbers): int
    {
        if ($this->bonus_numbers !== null && count($this->bonus_numbers) > 0) {
            foreach ($this->bonus_numbers as $number) {
                if ($type['bextra'] == 0 && in_array($number, $line_bnumbers)) {
                    $match_b++;
                }
                if ($type['bextra'] > 0 && in_array($number, $line_numbers)) {
                    $match_b++;
                }
            }
        }

        return $match_b;
    }

    public function match_others($match_others, $line_numbers): int
    {
        return 0;
    }

    protected function prepare_updated_lottery_model(): void
    {
        $additional_data = serialize($this->additional_data);
        $updated_lottery_values_set = [
            'last_update' => $this->now_utc_timezone->format(Helpers_Time::DATETIME_FORMAT),
            'additional_data' => $additional_data
        ];

        $is_jackpot_value_outdated = $this->get_jackpot_formatted() != $this->lottery['current_jackpot'];
        $should_draw_be_inserted = $is_jackpot_value_outdated ||
            $this->last_date_local === null ||
            $this->date > $this->last_date_local;
        if ($should_draw_be_inserted) {
            $this->insert_draw($updated_lottery_values_set, $is_jackpot_value_outdated, $additional_data);
        } elseif ($this->should_prizes_be_overwritten) {
            $this->override_prizes();
        }
        $this->lottery_to_update->set($updated_lottery_values_set);
    }

    /**
     * @throws Exception
     */
    public function process_lottery(): void
    {
        $this->prepare_jackpot();
        $this->sort_drawn_numbers();
        $this->prepare_last_date_local();
        $this->prepare_next_date_local();
        $this->prepare_updated_lottery_model();
        $this->lottery_to_update->save();

        if ($this->draw_has_been_changed) {
            $lottery = Lottery::find($this->lottery_to_update->id);
            $this->unscheduledDrawService->updateLotteryOnNewDraw($lottery, $this->previous_next_draw_date);
        }
    }

    protected function push_updated_jackpot_into_lottery_values_set(array &$updated_lottery_values_set): void
    {
        $updated_lottery_values_set['current_jackpot'] = $this->jackpot;
        $updated_lottery_values_set['current_jackpot_usd'] = Helpers_Currency::convert_to_USD(
            $this->jackpot,
            $this->lottery['currency']
        );
    }

    protected function get_jackpot_formatted(): string
    {
        return sprintf("%3.6f", $this->jackpot);
    }

    protected function send_jackpot_changed_emergency_email(array $updated_lottery_values_set): void
    {
        $debug = [
            $this->lottery,
            $updated_lottery_values_set,
            $this->last_date_local->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
            $this->date->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
            $this->next_date_local->format(Helpers_Time::DATETIME_NO_SECONDS_FORMAT),
            $this->jackpot
        ];
        $title = "Lotto Emergency: Jackpot changed: " . $this->lottery['name'];
        $body = "The jackpot has changed! More info: " .
            $this->get_jackpot_formatted() . " " .
            $this->lottery['current_jackpot'] . " " .
            var_export($debug, true) .
            " [helper.php/processLottery]";
        Config::load("lotteries", true);
        $recipients = Config::get("lotteries.emergency_emails");
        Helpers_Mail::send_emergency_email($title, $body, $recipients);
    }

    protected function send_time_limit_exceeded_emergency_email(): void
    {
        $title = "Lotto Emergency: Lottery Draw Date Mismatch";
        $body = "[important] It seems that draw date passed {$this->download_draw_hour_delay_limit}h ago for " .
            $this->lottery['name'] . "! It may mean that there is draw date " .
            "mismatch! This message will stop sending automatically after 1h. " .
            "[helper.php/processLottery].";
        Config::load("lotteries", true);
        $recipients = Config::get("lotteries.emergency_emails");
        Helpers_Mail::send_emergency_email($title, $body, $recipients);
    }


    protected function send_missed_draw_date_change_emergency_email(): void
    {
        $title = "Lotto Emergency: Lottery Draw Date Mismatch";
        $body = "[important] It seems we missed the draw date change " .
            "for lottery " . $this->lottery['name'] .
            ". Please fix it ASAP! [helper.php/processLottery].";
        Config::load("lotteries", true);
        $recipients = Config::get("lotteries.emergency_emails");
        Helpers_Mail::send_emergency_email($title, $body, $recipients);
    }

    protected function prepare_next_date_local(): void
    {
        if ($this->lottery['next_date_local'] !== null) {
            $next_date_local_str = $this->lottery['next_date_local'];
        } else {
            $next_date_local_str = Helpers_Time::UNIX_EPOCH_DATETIME;
        }
        $this->next_date_local = Carbon::parse($next_date_local_str, new DateTimeZone($this->lottery['timezone']));

        // If date is empty, we would not like to continue script processing
        if (empty($this->date) || !$this->date->format('Y-m-d')) {
            throw new Exception('Received empty date key.');
        }

        $next_draw_date_difference_from_now = intval(Lotto_View::date_diff($this->next_date_local, $this->now_utc_timezone, "%r%h"));

        if ($this->lottery_to_update->next_date_local == null || $this->lottery_to_update->next_date_local == $this->lottery_to_update->last_date_local) {
            if ($this->isKeno()) {
                $lastDrawDateLocal = Carbon::parse($this->lottery_to_update->last_date_local, $this->lottery['timezone']);

                // INFO:
                // $fromDatetime is used because Carbon requires reference date to calculate next draw date. Otherwise calculation may be off by 1 week.
                // $doesDelaysApply is set to false because it applies to Superena Lotto only.
                $act_local_date = Lotto_Helper::get_lottery_next_draw($this->lottery_to_update, false, $lastDrawDateLocal, 1);
            } else {
                $act_local_date = Lotto_Helper::get_lottery_next_draw($this->lottery_to_update);
            }
            $dbdate = $act_local_date->format(Helpers_Time::DATETIME_FORMAT);
            $act_local_date->setTimezone(new DateTimeZone("UTC"));
            $this->lottery_to_update->set(
                [
                    'next_date_local' => $dbdate,
                    'next_date_utc' => $act_local_date->format(Helpers_Time::DATETIME_FORMAT)
                ]
            );
            Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);

            return;
        }
        if ($next_draw_date_difference_from_now === $this->download_draw_hour_delay_limit) {
            $this->send_time_limit_exceeded_emergency_email();

            return;
        }

        if ($this->next_date_local < $this->last_date_local) {
            $this->send_missed_draw_date_change_emergency_email();

            return;
        }
    }

    /** If lottery has no last_date_local then add slack log */
    protected function prepare_last_date_local(): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if ($this->lottery['last_date_local'] === null) {
            $warning = "Lottery with id: " . $this->lottery_to_update->id . " has no last_date_local.";

            $fileLoggerService->warning(
                $warning
            );
            return;
        }
        $this->last_date_local = Carbon::parse(
            $this->lottery['last_date_local'],
            new DateTimeZone($this->lottery['timezone'])
        );
    }

    protected function prepare_jackpot(): void
    {
        $this->jackpot = $this->jackpot ?: null;
    }

    protected function override_prizes(): void
    {
        // for powerball, we need to update prizes some time after the draw
        $fdraw = Model_Lottery_Draw::get_draw_by_date($this->lottery, $this->date);

        if ($fdraw !== null) {
            $fprize = Model_Lottery_Prize_Data::get_draw_prize_data($fdraw);
            if (!isset($this->prizes[0][1])) {
                if ($this->prizes[0][0] != 0) {
                    $this->prizes[0][1] = $fdraw['jackpot'] * 1000000;
                } else {
                    $this->prizes[0][1] = 0;
                }
            }
            if ($this->prizes[0][0] == 0) {
                $this->prizes[0][1] = 0;
            }
            $total_winners = 0;
            $total_prize = 0;
            foreach ($this->prizes as $prize) {
                $total_winners += $prize[0];
                $total_prize += $prize[1] * $prize[0];
            }

            if ($fdraw["total_winners"] != $total_winners || $fdraw["total_prize"] != $total_prize) {
                $db_draw = Model_Lottery_Draw::find_by_pk($fdraw["id"]);
                $db_draw->set([
                    'total_winners' => $total_winners,
                    'total_prize' => $total_prize
                ]);
                $db_draw->save();
                Lotto_Helper::clear_cache(['model_lottery_draw']);
            }
            foreach ($fprize as $key => $fprize_item) {
                if ($fprize_item['winners'] != $this->prizes[$key][0] || $fprize_item['prizes'] != $this->prizes[$key][1]) {
                    $db_fprize_item = Model_Lottery_Prize_Data::find_by_pk($fprize_item["id"]);
                    $db_fprize_item->winners = $this->prizes[$key][0];
                    $db_fprize_item->prizes = $this->prizes[$key][1];
                    $db_fprize_item->save();
                    Lotto_Helper::clear_cache(['model_lottery_draw', 'model_lottery_prize_data']);
                    Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
                }
            }
        }
    }

    /**
     * @param array  $updated_lottery_values_set
     * @param bool   $is_jackpot_value_outdated
     * @param string $additional_data
     *
     * @return void
     * @throws Exception
     */
    protected function insert_draw(array &$updated_lottery_values_set, bool $is_jackpot_value_outdated, string $additional_data): void
    {
        $draw = null;
        $is_jackpot_set = $this->jackpot !== null;
        $system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");
        $lottery_type_data_part = $this->get_lottery_data_part();
        if (
            $is_jackpot_set &&
            ($this->lottery['draw_jackpot_set'] === 0 || $this->overwrite_jackpot)
        ) {
            $this->push_updated_jackpot_into_lottery_values_set($updated_lottery_values_set);
            $updated_lottery_values_set['draw_jackpot_set'] = 1;
        } else {
            // I have moved that checking over here to make some clearance
            $next_date_local_with_margin = clone $this->next_date_local;
            $next_date_local_with_margin->subHours(self::JACKPOT_UPDATE_MARGIN);
            $next_date_local_checked = $this->date->diff($this->next_date_local) === false ||
                $this->now_lottery_timezone >= $next_date_local_with_margin;

            if (
                $this->last_date_local !== null &&
                $this->date <= $this->last_date_local &&
                $next_date_local_checked === false &&
                $is_jackpot_set &&
                $this->lottery['draw_jackpot_set'] == 1 &&
                $is_jackpot_value_outdated
            ) {
                $this->push_updated_jackpot_into_lottery_values_set($updated_lottery_values_set);
                $this->send_jackpot_changed_emergency_email($updated_lottery_values_set);
            }
        }

        if (($this->last_date_local === null || $this->date > $this->last_date_local) &&
            $this->validate_draw_date($this->lottery, $this->date) === true
        ) {
            try {
                DB::start_transaction();
                // make jackpot pending
                if ($is_jackpot_set === false) {
                    $updated_lottery_values_set['current_jackpot'] = null;
                    $updated_lottery_values_set['current_jackpot_usd'] = null;
                }
                if (in_array($this->lottery['id'], $this->lottery_noestimated_ids)) {
                    $updated_lottery_values_set['estimated_updated'] = 0;
                }
                $updated_lottery_values_set['last_date_local'] = $this->date->format(Helpers_Time::DATETIME_FORMAT);
                $updated_lottery_values_set['last_numbers'] = implode(',', $this->numbers);
                $updated_lottery_values_set['last_bnumbers'] = !empty($this->bonus_numbers) ? implode(',', $this->bonus_numbers) : null;
                $type = Model_Lottery_Type::get_lottery_type_for_date($this->lottery, $this->date->format('Y-m-d'));
                if ($type === null) {
                    throw new Exception('Helper - No lottery type.');
                }
                $type = Model_Lottery_Type::forge($type);

                $draw = Model_Lottery_Draw::forge();
                $draw->set([
                    'lottery_id' => $this->lottery['id'],
                    'date_download' => $this->now_utc_timezone->format('Y-m-d H:i:s'),
                    'date_local' => $this->date->format(Helpers_Time::DATETIME_FORMAT),
                    'jackpot' => $this->lottery_to_update->current_jackpot,
                    'numbers' => $updated_lottery_values_set['last_numbers'],
                    'bnumbers' => $updated_lottery_values_set['last_bnumbers'],
                    'lottery_type_id' => $type['id'],
                    'total_prize' => 0,
                    'total_winners' => 0,
                    'final_jackpot' => 0,
                    'additional_data' => $additional_data
                ]);
                //$draw->save(); // TODO: Check if not saving here (before that possible exception being thrown) won't break the logic
                // now prizes

                $type_data = Model_Lottery_Type_Data::find_by_lottery_type_id($type['id']);

                if ($this->isNotKeno()) {
                    if ($this->prizes === null || !count($this->prizes)) {
                        throw new Exception('Helper - No prizes!');
                    }

                    if (count($type_data) != count($this->prizes)) {
                        throw new Exception('Helper - Type-prize mismatch');
                    }

                    if (!isset($this->prizes[0][1])) {
                        if ($this->prizes[0][0] != 0) {
                            $this->prizes[0][1] = $draw->jackpot * 1000000;
                        } else {
                            $this->prizes[0][1] = 0;
                        }
                    }

                    if ($this->prizes[0][0] == 0) {
                        $this->prizes[0][1] = 0;
                    }

                    $total_winners = 0;
                    $total_prize = 0;

                    foreach ($this->prizes as $prize) {
                        $total_winners += $prize[0];
                        $total_prize += $prize[1] * $prize[0];
                    }

                    $finalJackpot = $this->prizes[0][1];
                } else {
                    $total_winners = 0;
                    $total_prize = 0;
                    $finalJackpot = 0;
                }

                $draw->set([
                    'final_jackpot' => $finalJackpot,
                    'total_winners' => $total_winners,
                    'total_prize' => $total_prize
                ]);
                $draw->save();
                $this->insert_draw_for_whitelabels($draw);

                if ($this->isNotKeno()) {
                    $this->insert_prize_data($draw, $type_data);
                }

                // update lottery
                $updated_lottery_values_set['last_total_prize'] = $total_prize;
                $updated_lottery_values_set['last_total_winners'] = $total_winners;
                $updated_lottery_values_set['last_jackpot_prize'] = $finalJackpot;
                $updated_lottery_values_set['draw_jackpot_set'] = 0;

                if ($is_jackpot_set) {
                    $updated_lottery_values_set['draw_jackpot_set'] = 1;
                    $this->push_updated_jackpot_into_lottery_values_set($updated_lottery_values_set);
                }

                $tickets = Model_Whitelabel_User_Ticket::find_pending_for_draw($draw);
                /* Prize calculations */
                if ($tickets != null && count($tickets) > 0) {
                    $this->update_tickets_and_lines_with_prizes($tickets, $type, $type_data, $system_currency_tab, $lottery_type_data_part);
                }

                // update multi-draw current_draw and is finished flags
                Helpers_Multidraw::update_current_draw_date($this->lottery, $this->last_date_local, $this->date);
                Helpers_Multidraw::mark_as_finished($this->lottery, $this->date);

                // Set draw id to draw email notifications list
                $notification_draw_helper = new Helpers_Notifications_Draw();
                $notification_draw_helper->update_draw_notification_emails($draw['lottery_id'], $draw['date_local'], $draw['id']);

                DB::commit_transaction();
            } catch (Exception $e) {
                DB::rollback_transaction();
                throw $e;
            }
            Lotto_Helper::clear_cache(['model_lottery_draw', 'model_lottery_prize_data']);
            if ($this->isNotKeno()) {
                $pageCache = Container::get(PageCacheService::class);
                $pageCache->clearAllActiveWhitelabels();
            }
        }
        Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
    }

    protected function isNotKeno(): bool
    {
        return $this->lottery['type'] !== Helpers_Lottery::TYPE_KENO;
    }

    protected function isKeno(): bool
    {
        return $this->lottery['type'] === Helpers_Lottery::TYPE_KENO;
    }

    protected function insert_draw_for_whitelabels(Model_Lottery_Draw $draw): void
    {
        // we have the draw, let's add it to whitelabels who have this lottery enabled
        $draw_whitelabel_lotteries = Model_Whitelabel_Lottery::find_by_lottery_id($this->lottery['id']);
        if ($draw_whitelabel_lotteries !== null) {
            foreach ($draw_whitelabel_lotteries as $draw_whitelabel_lottery) {
                $draw_db = Model_Whitelabel_Lottery_Draw::forge();
                $draw_db->set([
                    'whitelabel_id' => $draw_whitelabel_lottery->whitelabel_id,
                    'lottery_draw_id' => $draw->id
                ]);
                $draw_db->save();
            }
        }
    }

    protected function insert_prize_data(Model_Lottery_Draw $draw, array $type_data): void
    {
        foreach ($this->prizes as $key => $value) {
            $prize_data = Model_Lottery_Prize_Data::forge();
            $prize_data->set(
                [
                    'lottery_draw_id' => $draw->id,
                    'lottery_type_data_id' => $type_data[$key]['id'],
                    'winners' => $value[0],
                    'prizes' => $value[1],
                    'lottery_type_multiplier_id' => null,
                ]
            );
            $prize_data->save();
        }
    }

    protected function get_lottery_data_part(): array
    {
        return [
            Helpers_General::LOTTERY_TYPE_DATA_PRIZE,
            Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED,
        ];
    }

    protected function sort_drawn_numbers(): void
    {
        asort($this->numbers);
        if (empty($this->bonus_numbers) === false) {
            asort($this->bonus_numbers);
        }
    }

    protected function update_tickets_and_lines_with_prizes(array $tickets, ?Model_Lottery_Type $type, ?array $type_data, array $system_currency_tab, array $lottery_type_data_part): void
    {
        // NOTE: for keno fetch keno_data which contains multiplier and count of numbers selected. and is selectable by ticket id
        if ($this->isKeno()) { // NOTE: I decided to fetch them separately as less intrusive method (and with less side effects) and simpler method. overhead in script is tolerable. it is not I/O operation.
            $ticketsIds = [];
            foreach ($tickets as $ticket) {
                $ticketsIds[] = $ticket['id'];
            }
            $ticketsKenoDatumRaw = Model_Whitelabel_User_Ticket_Keno_Data::byTicketIds($ticketsIds);
            $ticketsKenoDatum = [];
            foreach ($ticketsKenoDatumRaw as $ticketsKenoData) {
                $ticketsKenoDatum[$ticketsKenoData['whitelabel_user_ticket_id']] = $ticketsKenoData;
            }
            unset($ticketsKenoDatumRaw);
        }
        $currencies = Helpers_Currency::getCurrencies();
        foreach ($tickets as $ticket) {
            if ($ticket->paid == Helpers_General::TICKET_PAID) {
                $whitelabel = Model_Whitelabel::find_by_pk($ticket->whitelabel_id);
                if ($whitelabel == null) {
                    throw new Exception('Couldn\'t find whitelabel');
                }

                $user_group_payout_percents = Model_Whitelabel_User_Group::payout_percents_for_whitelabel($whitelabel['id']); // TODO: refactor - not optimal. pending overhaul

                $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    $whitelabel['manager_site_currency_id']
                );

                $wlotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
                $wlottery = $wlotteries['__by_id'][$this->lottery['id']];

                $ticket_total_prize = 0;
                $ticket_total_prize_usd = 0;
                $ticket_total_prize_user = 0;
                $ticket_total_prize_manager = 0;

                $ticket_total_prize_net = 0;
                $ticket_total_prize_net_usd = 0;
                $ticket_total_prize_net_user = 0;
                $ticket_total_prize_net_manager = 0;

                $user_total_prize_net_user = 0;
                $total_payout = Helpers_General::TICKET_PAYOUT_PAIDOUT;
                $total_status = Helpers_General::TICKET_STATUS_NO_WINNINGS;
                $quickpick = 0;
                $total_is_jackpot = false;

                // count matches
                $lines = Model_Whitelabel_User_Ticket_Line::find_by_whitelabel_user_ticket_id($ticket->id);
                $auser = Model_Whitelabel_User::find_by_pk($ticket->whitelabel_user_id);
                if ($lines !== null && $auser != null) {
                    foreach ($lines as $line) {
                        $line_numbers = explode(',', $line->numbers);
                        $line_numbers = array_map(function ($val) {
                            return intval($val);
                        }, $line_numbers);

                        $line_bnumbers = explode(',', $line->bnumbers);
                        $line_bnumbers = array_map(function ($val) {
                            return intval($val);
                        }, $line_bnumbers);

                        $match_n = 0;
                        $match_b = 0;
                        $match_others_temp = 0;

                        $prize = 0;
                        $prize_usd = 0;
                        $prize_user = 0;
                        $prize_manager = 0;

                        $prize_net = 0;
                        $prize_net_usd = 0;
                        $prize_net_user = 0;
                        $prize_net_manager = 0;

                        $uncovered_prize = 0;
                        $uncovered_prize_usd = 0;
                        $uncovered_prize_user = 0;
                        $uncovered_prize_manager = 0;

                        $status = Helpers_General::TICKET_STATUS_NO_WINNINGS;
                        $match_type = null;
                        $is_jackpot = false;
                        $is_pending_quickpick = false;
                        $match_n = $this->match_n($match_n, $line_numbers);
                        $match_b = $this->match_b($match_b, $type, $line_bnumbers, $line_numbers);
                        $match_others = $this->match_others($match_others_temp, $line);

                        // check which type_data matches

                        if ($this->isKeno() || $this->check_match($match_n, $match_b, $match_others)) {
                            foreach ($type_data as $winkey => $wintype) {
                                if ($this->isNotKeno()) {
                                    $wintype_check = $this->is_type_data_winning(
                                        $type,
                                        $wintype,
                                        $match_n,
                                        $match_b,
                                        $match_others
                                    );
                                } else {
                                    $wintype_check = $this->isKenoTypeDataWinning($wintype, $match_n, $ticketsKenoDatum[$ticket->id]);
                                }
                                if ($wintype_check) {
                                    $status = Helpers_General::TICKET_STATUS_WIN;
                                    $total_status = Helpers_General::TICKET_STATUS_WIN;

                                    $match_type = $wintype['id'];
                                    if ($wintype['is_jackpot']) {
                                        $is_jackpot = true;
                                        $total_is_jackpot = true;
                                        // jackpot win!
                                        // basically do nothing, as we don't want to calculate the prize
                                        // as it's not known yet
                                        // and for sure we don't want to pay the jackpot to user balance
                                    } elseif ($wintype['type'] == Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                                        $quickpick++;
                                        $provider = Model_Lottery_Provider::find_by_pk($ticket->lottery_provider_id);

                                        // was used for imvalap, now used also for lottorisq insurance or "none model"
                                        if (
                                            $provider['provider'] != 1 ||
                                            ($ticket->model == Helpers_General::LOTTERY_MODEL_MIXED &&
                                                $ticket->is_insured = 1) ||
                                            $ticket->model == Helpers_General::LOTTERY_MODEL_NONE
                                        ) {
                                            // valid to is a relict of manual quickpick
                                            $valid_to = Lotto_Helper::get_lottery_next_draw(
                                                $this->lottery,
                                                true,
                                                null,
                                                2
                                            );

                                            $type = Model_Lottery_Type::get_lottery_type_for_date(
                                                $this->lottery,
                                                $valid_to->format("Y-m-d")
                                            );

                                            /*** price calculations ***/ /* TODO: currency manipulation? */

                                            $model = $wlottery['model'];

                                            $is_insured = false;
                                            $tier = 0;
                                            if (
                                                $model == Helpers_General::LOTTERY_MODEL_MIXED &&
                                                Lotto_Helper::should_insure($wlottery, $wlottery['tier'], $wlottery['volume'])
                                            ) {
                                                $is_insured = true;
                                                $tier = $wlottery['tier'];
                                            }

                                            $calc_cost = Lotto_Helper::get_price(
                                                $wlottery,
                                                $wlottery['model'],
                                                $wlottery['tier'],
                                                $wlottery['volume']
                                            );

                                            $amount = 0;
                                            $amount_usd = 0;
                                            $amount_payment = 0;
                                            $amount_local = 0;
                                            $amount_manager = 0;

                                            $cost_local = $calc_cost[0] + $calc_cost[1];
                                            $cost_usd = Helpers_Currency::convert_to_USD($cost_local, $wlottery['currency']);
                                            $cost = Helpers_Currency::get_recalculated_to_given_currency(
                                                $cost_usd,
                                                $system_currency_tab,
                                                $currencies[$auser['currency_id']]['code']
                                            );
                                            $cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
                                                $cost_usd,
                                                $system_currency_tab,
                                                $manager_currency_tab['code']
                                            );

                                            $income_local = 0 - $cost_local; // -ticketcost
                                            $income_usd = 0 - $cost_usd; // -ticketcost
                                            $income = 0 - $cost; //-ticketcost
                                            $income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                                                $income_usd,
                                                $system_currency_tab,
                                                $manager_currency_tab['code']
                                            );

                                            $income_value = $wlottery['income'];
                                            $income_type = $wlottery['income_type'];

                                            $margin_value = $whitelabel['margin'];

                                            $margin_local = $cost_local * $margin_value;
                                            $margin_usd = $cost_usd * $margin_value;
                                            $margin = $cost * $margin_value;
                                            $margin_manager = $cost_manager * $margin_value;

                                            if (Helpers_Lottery::isGgrEnabled($this->lottery['type'])) {
                                                $margin_value = 0;
                                                $margin_local = 0.00;
                                                $margin_usd = 0.00;
                                                $margin = 0.00;
                                                $margin_manager = 0.00;
                                            }
                                            /*** end of price calculations ***/

                                            // give free ticket
                                            $ticket_free = Model_Whitelabel_User_Ticket::forge();
                                            $ticket_free->set([
                                                'token' => Lotto_Security::generate_ticket_token($whitelabel['id']),
                                                'whitelabel_id' => $whitelabel['id'],
                                                'whitelabel_user_id' => $auser['id'],
                                                'lottery_id' => $this->lottery['id'],
                                                'lottery_type_id' => $type['id'],
                                                'currency_id' => $ticket->currency_id,
                                                'valid_to_draw' => $valid_to->format(Helpers_Time::DATETIME_FORMAT),
                                                'draw_date' => $valid_to->format(Helpers_Time::DATETIME_FORMAT),
                                                'amount' => $amount,
                                                'amount_usd' => $amount_usd,
                                                'amount_payment' => $amount_payment,
                                                'amount_local' => $amount_local,
                                                'amount_manager' => $amount_manager,
                                                'date' => DB::expr("NOW()"),
                                                'status' => Helpers_General::TICKET_STATUS_PENDING,
                                                'paid' => Helpers_General::TICKET_PAID,
                                                'payout' => Helpers_General::TICKET_PAYOUT_PENDING,
                                                'model' => $model,
                                                'is_insured' => $is_insured,
                                                'tier' => $tier,
                                                'cost_local' => $cost_local,
                                                'cost_usd' => $cost_usd,
                                                'cost' => $cost,
                                                'cost_manager' => $cost_manager,
                                                'income_local' => $income_local,
                                                'income_usd' => $income_usd,
                                                'income' => $income,
                                                'income_value' => $income_value,
                                                'income_manager' => $income_manager,
                                                'income_type' => $income_type,
                                                'margin_value' => $margin_value,
                                                'margin_local' => $margin_local,
                                                'margin_usd' => $margin_usd,
                                                'margin' => $margin,
                                                'margin_manager' => $margin_manager,
                                                'bonus_cost_local' => $cost_local,
                                                'bonus_cost_usd' => $cost_usd,
                                                'bonus_cost' => $cost,
                                                'bonus_cost_manager' => $cost_manager,
                                                'ip' => $ticket->ip,
                                                'line_count' => 1,
                                            ]);
                                            $ticket_free->save();
                                            //                                                            $auser->total_net_income = bcadd($auser->total_net_income, $income_usd, 2);
                                            //                                                            $auser->pnl = bcadd($auser->pnl, bcdiv($auser->total_net_income, 2), 2);
                                            //                                                          // $auser->save();

                                            // decrease whitelabel prepaid
                                            if ((int)$wlottery['should_decrease_prepaid'] === 1) {
                                                $prepaid = new Forms_Admin_Whitelabels_Prepaid_New($whitelabel->to_array());
                                                $prepaid->subtract_prepaid($ticket_free['cost_manager'], null, false);
                                            }

                                            // let's make random quick pick
                                            $brandom = [];
                                            $random = Lotto_Helper::get_random_values($type['ncount'], $type['nrange']);
                                            if ($type['bextra'] == 0 && $type['bcount'] > 0) {
                                                $brandom = Lotto_Helper::get_random_values($type['bcount'], $type['brange']);
                                            }

                                            $ticketline = Model_Whitelabel_User_Ticket_Line::forge();
                                            $ticketline->set([
                                                'whitelabel_user_ticket_id' => $ticket_free->id,
                                                'numbers' => implode(',', $random),
                                                'bnumbers' => implode(',', $brandom),
                                                'amount' => $amount,
                                                'amount_usd' => $amount_usd,
                                                'amount_local' => $amount_local,
                                                'amount_payment' => $amount_payment,
                                                'amount_manager' => $amount_manager,
                                                'status' => Helpers_General::TICKET_STATUS_PENDING,
                                                'payout' => Helpers_General::TICKET_PAYOUT_PENDING
                                            ]);
                                            $ticketline->save();
                                        } else {
                                            // lottorisq has native implementation of lucky dip
                                            // given for next draw
                                            // see task/providerscheck

                                            // let's make the paidout pending until lottorisq give us random nums
                                            $is_pending_quickpick = true;
                                        }
                                    } elseif (in_array($wintype['type'], $lottery_type_data_part)) {
                                        // percentage or constant value
                                        // special case for insurance and not-yet-known prize, we use estimated (previously insured) value
                                        if (
                                            $wintype['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED &&
                                            $ticket->model == Helpers_General::LOTTERY_MODEL_MIXED &&
                                            $ticket->is_insured &&
                                            ($winkey + 1) <= $ticket->tier
                                        ) {
                                            $prize = $wintype['estimated'];
                                        } elseif ($this->isNotKeno() && ($this->prizes[$winkey][0] &&
                                            $this->prizes[$winkey][1] != "0"
                                        )) {
                                            $prize = $this->prizes[$winkey][1];
                                        } else {
                                            if ($wintype['type'] == Helpers_General::LOTTERY_TYPE_DATA_PRIZE) {
                                                $prize = $wintype['prize'];
                                            } elseif ($wintype['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED) {
                                                $prize = $wintype['estimated'];
                                            }
                                        }

                                        if ($this->isNotKeno()) {
                                            $provider = Model_Lottery_Provider::find_by_pk($ticket->lottery_provider_id);
                                            $tax_percentage = $provider['tax'];
                                            $tax_min_amount = $provider['tax_min'];
                                        } else {
                                            $tax_percentage = 0;
                                            $tax_min_amount = 0;
                                        }

                                        $lottery_currency_rate = $currencies[$this->lottery['currency_id']]['rate'];
                                        $multiplier_usd = round(1 / $lottery_currency_rate, 4);
                                        $prize_usd = round($prize * $multiplier_usd, 2);

                                        $auser_currency_rate = $currencies[$auser['currency_id']]['rate'];
                                        $prize_user = round($prize_usd * $auser_currency_rate, 4);

                                        $prize_manager = Helpers_Currency::get_recalculated_to_given_currency(
                                            $prize_usd,
                                            $system_currency_tab,
                                            $manager_currency_tab['code']
                                        );

                                        $prize_net = $prize;
                                        if (round($prize_net, 2) > $tax_min_amount) {
                                            $tax_div = round($tax_percentage / 100, 2);
                                            $price_mul = round($prize * $tax_div, 2);
                                            $prize_net = round($prize_net - $price_mul, 2);
                                        }
                                        $prize_net_usd = round($prize_net * $multiplier_usd, 2);
                                        $prize_net_user = round($prize_net_usd * $auser_currency_rate, 2);

                                        $prize_net_manager = Helpers_Currency::get_recalculated_to_given_currency(
                                            $prize_net_usd,
                                            $system_currency_tab,
                                            $manager_currency_tab['code']
                                        );

                                        if (
                                            $wintype['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED &&
                                            (($ticket->model == Helpers_General::LOTTERY_MODEL_MIXED &&
                                                $ticket->is_insured &&
                                                ($winkey + 1) > $ticket->tier) ||
                                                $ticket->model == Helpers_General::LOTTERY_MODEL_NONE)
                                        ) {
                                            $uncovered_prize = round($uncovered_prize + $prize_net, 2);
                                            $uncovered_prize_user = round($uncovered_prize_usd + $prize_net_usd, 2);
                                            $uncovered_prize_user = round($uncovered_prize_user + $prize_net_user, 2);

                                            $uncovered_prize_manager = Helpers_Currency::get_recalculated_to_given_currency(
                                                $uncovered_prize_usd,
                                                $system_currency_tab,
                                                $manager_currency_tab['code']
                                            );
                                        }
                                    }
                                    break; // we found the prize, no need to look for next prize
                                }
                            }
                        }

                        if ($this->isKeno()) {
                            $jackpot = $this->lottery['current_jackpot'] * 1000000;
                            $jackpotUSD = $this->lottery['current_jackpot_usd'] * 1000000;

                            [
                                $prize,
                                $prize_usd,
                                $prize_user,
                                $prize_manager,
                                $prize_net,
                                $prize_net_usd,
                                $prize_net_user,
                                $prize_net_manager,
                                $uncovered_prize,
                                $uncovered_prize_usd,
                                $uncovered_prize_user,
                                $uncovered_prize_manager,
                            ] = [
                                round(min($prize * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),                     // local currency (EUR, GBP, USD etc)
                                round(min($prize_usd * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpotUSD), 2),              // USD
                                round(min($prize_user * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),                // local currency (EUR, GBP, USD etc)
                                round(min($prize_manager * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),             // local currency (EUR, GBP, USD etc)
                                round(min($prize_net * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),                 // local currency (EUR, GBP, USD etc)
                                round(min($prize_net_usd * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpotUSD), 2),          // USD
                                round(min($prize_net_user * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),            // local currency (EUR, GBP, USD etc)
                                round(min($prize_net_manager * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),         // local currency (EUR, GBP, USD etc)

                                round(min($uncovered_prize * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),           // local currency (EUR, GBP, USD etc)
                                round(min($uncovered_prize_usd * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpotUSD), 2),    // USD
                                round(min($uncovered_prize_user * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),      // local currency (EUR, GBP, USD etc)
                                round(min($uncovered_prize_manager * $ticketsKenoDatum[$ticket->id]['multiplier'], $jackpot), 2),   // local currency (EUR, GBP, USD etc)
                            ];
                        }
                        $line->set([
                            'status' => $status,
                            'prize_local' => $prize,
                            'prize_usd' => $prize_usd,
                            'prize' => $prize_user,
                            'prize_manager' => $prize_manager,
                            'prize_net_local' => $prize_net,
                            'prize_net_usd' => $prize_net_usd,
                            'prize_net' => $prize_net_user,
                            'prize_net_manager' => $prize_net_manager,
                            'uncovered_prize_local' => $uncovered_prize,
                            'uncovered_prize_usd' => $uncovered_prize_usd,
                            'uncovered_prize' => $uncovered_prize_user,
                            'uncovered_prize_manager' => $uncovered_prize_manager,
                            'lottery_type_data_id' => $match_type
                        ]);

                        $payout = Helpers_General::TICKET_PAYOUT_PENDING;
                        // if less than whitelabel top amount

                        $ticket_total_prize = round($ticket_total_prize + $prize, 2);
                        $ticket_total_prize_usd = round($ticket_total_prize_usd + $prize_usd, 2);
                        $ticket_total_prize_user = round($ticket_total_prize_user + $prize_user, 2);
                        $ticket_total_prize_manager = round($ticket_total_prize_manager + $prize_manager, 2);

                        $ticket_total_prize_net = round($ticket_total_prize_net + $prize_net, 2);
                        $ticket_total_prize_net_usd = round($ticket_total_prize_net_usd + $prize_net_usd, 2);
                        $ticket_total_prize_net_user = round($ticket_total_prize_net_user + $prize_net_user, 2);
                        $ticket_total_prize_net_manager = round($ticket_total_prize_net_manager + $prize_net_manager, 2);

                        if (
                            $prize_net_usd <= $whitelabel['max_payout'] &&
                            !$is_jackpot && !$is_pending_quickpick
                        ) {
                            $payout = Helpers_General::TICKET_PAYOUT_PAIDOUT;
                            $user_total_prize_net_user += $prize_net_user;
                        } else {
                            $total_payout = Helpers_General::TICKET_PAYOUT_PENDING;
                        }

                        $line->set([
                            'payout' => $payout
                        ]);
                        $line->save();
                    }

                    //
                    $percent = 100.00;
                    if (
                        !empty($user_group_payout_percents)
                        && array_key_exists($auser->prize_payout_whitelabel_user_group_id, $user_group_payout_percents)
                    ) {
                        $percent = $user_group_payout_percents[$auser->prize_payout_whitelabel_user_group_id];
                    }

                    //////
                    $ticket->set([
                        "status" => $total_status,
                        "date_processed" => DB::expr("NOW()"),
                        "prize" => $ticket_total_prize_user,
                        "prize_manager" => $ticket_total_prize_manager,
                        "prize_local" => $ticket_total_prize,
                        "prize_usd" => $ticket_total_prize_usd,
                        "prize_net" => $ticket_total_prize_net_user,
                        "prize_net_manager" => $ticket_total_prize_net_manager,
                        "prize_net_local" => $ticket_total_prize_net,
                        "prize_net_usd" => $ticket_total_prize_net_usd,
                        'payout' => $total_payout,
                        'prize_jackpot' => intval($total_is_jackpot),
                        'prize_quickpick' => $quickpick,
                        'prize_payout_percent' => $percent,
                    ]);
                    $ticket->save();

                    if ($user_total_prize_net_user > 0) {
                        $balance_change_amount = $user_total_prize_net_user * $percent / 100;

                        $user_update_query = DB::query(
                            "UPDATE whitelabel_user 
                                 SET balance = balance + :balance_change_amount,
                                 pnl_manager = COALESCE(pnl_manager, 0) - :net_amount, 
                                 net_winnings_manager = COALESCE(net_winnings_manager, 0) + :net_amount, 
                                 last_update = NOW()
                                 WHERE whitelabel_user.id = :user_id"
                        );
                        $user_update_query->param(":balance_change_amount", $balance_change_amount);
                        $user_update_query->param(":net_amount", $ticket_total_prize_net_manager);
                        $user_update_query->param(":user_id", $auser->id);

                        $user_update_query->execute();

                        $updated_user = Model_Whitelabel_User::find_by_pk($auser->id);

                        $this->event->register('deposit_update', 'Events_Deposit_Update::handle');
                        $this->event->trigger('deposit_update', [
                            'whitelabel_id' => $whitelabel['id'],
                            'user_id' => $auser->id,
                            'plugin_data' => [
                                'balance' => $updated_user['balance'],
                                'casino_balance' => $updated_user['casino_balance'],
                                'net_winnings_manager' => $updated_user['net_winnings_manager'],
                                'pnl_manager' => $updated_user['pnl_manager']
                            ],
                        ]);
                    }
                    ///////
                }
            } else {
                $ticket->set([
                    "date_processed" => DB::expr("NOW()")
                ]);
                $ticket->save();
            }
        }
    }

    /**
     * Sends a delayed error log.
     * 
     * @param string $slug The lottery slug.
     * @param string $errorMessage The error message to be logged (Validation, Both Sources etc.).
     * @param Throwable $throwable The throwable object representing the error or exception.
     * @param int $delayInHours The delay time for sending the error log, in hours. Defaults to 5 hours.
     * 
     * @return void
     */
    public function sendDelayedErrorLog(string $slug, string $errorMessage, \Throwable $exception, ?string $nextDrawDateFormatted = null, int $delayInHours = 4): void
    {
        $cacheKey = str_replace([' ', '-', '_'], '', $slug . $errorMessage . 'ErrorAttempt');
        if ($nextDrawDateFormatted) {
            $cacheKey .= $nextDrawDateFormatted;
        }
        $fileLoggerService = Container::get(FileLoggerService::class);

        $fileLoggerService->shouldSendLogWhenProblemExistsAfterGivenTime(
            $delayInHours,
            "[{$slug}] {$errorMessage} Failed. Intervention required.
            Details: {$exception->getMessage()}",
            $cacheKey,
            FileLoggerService::LOG_TYPE_ERROR
        );
    }
}
