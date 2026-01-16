<?php

use Carbon\Carbon;
use Models\LottorisqLog;
use Repositories\LotteryRepository;
use Repositories\LottorisqLogRepository;
use Repositories\LtechManualDrawRepository;

abstract class Lotto_Lotteries_Ltech extends Lotto_Lotteries_Lottery
{
    /**
     *
     * @var string
     */
    protected $ltech_slug = "";

    /**
     * Name of bonus numbers in l-tech api for certain lottery
     *
     * @var string
     */
    protected $ltech_bonus_name = '';

    /**
     *
     * @var array
     */
    protected $additional_data = [];

    /** @var array */
    protected $lottery_ltech_ignored_slugs = [];

    /**
     * @var Carbon
     */
    protected $ltech_next_draw_date = null;

    /**
     * @return void
     */
    public function get_results(): void
    {
        $lottery = $this->lottery;
        if ($this->download_draw($lottery) === true) {
            parent::set_lottery_with_data(
                $lottery,
                $this->jackpot,
                $this->date,
                $this->date_utc,
                $this->numbers,
                $this->bonus_numbers,
                $this->prizes,
                $this->overwrite_jackpot,
                $this->should_prizes_be_overwritten,
                $this->additional_data
            );
        }
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
            $next_draw_date_calculated = Lotto_Helper::get_lottery_next_draw($this->lottery_to_update);
            if ($this->ltech_next_draw_date !== null) {
                // we assume $this->ltech_next_draw_date is instance of Carbon
                $act_local_date = $this->ltech_next_draw_date;
                if ($act_local_date->notEqualTo($next_draw_date_calculated)) {
                    Helpers_Mail::send_emergency_email(
                        "Lotto Emergency: Lottery next draw date WARNING",
                        sprintf("It seems that the LTECH next draw date %s is inconsistent with the next draw date %s for the lottery: %s.",
                            $act_local_date->format(Helpers_Time::DATETIME_FORMAT),
                            $next_draw_date_calculated->format(Helpers_Time::DATETIME_FORMAT),
                            $this->lottery['name']
                        )
                    );

                    $this->draw_has_been_changed = true;
                    $this->previous_next_draw_date = $next_draw_date_calculated;
                }
            } else {
                $act_local_date = $next_draw_date_calculated;
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
        } elseif ($next_draw_date_difference_from_now === $this->download_draw_hour_delay_limit) {
            $this->send_time_limit_exceeded_emergency_email();
        } elseif ($this->next_date_local < $this->last_date_local) {
            $this->send_missed_draw_date_change_emergency_email();
        }
    }

    /**
     *
     * @param array $lottery
     *
     * @return bool
     */
    public function download_draw(array $lottery): bool
    {
        if (empty($this->ltech_slug)) {
            return false;
        }

        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        try {
            Config::load("lottorisq", true);

            $endpoint = Config::get("lottorisq.lottorisq.endpoint");
            $key = Config::get("lottorisq.lottorisq.key");
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $endpoint . 'draws/' . $this->ltech_slug);
            curl_setopt($ch, CURLOPT_USERPWD, $key);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type:application/json",
                "Cache-Control:no-cache"
            ]);

            $ssl_verifypeer = 2;
            $ssl_verifyhost = 2;
            if (Helpers_General::is_development_env()) {
                $ssl_verifypeer = 0;
                $ssl_verifyhost = 0;
            }

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

            $lotteryRepository = Container::get(LotteryRepository::class);
            $lotteryOrm = $lotteryRepository->findOneBySlug($lottery['slug']);

            /** This is feature allows to enter draw results manually */
            $ltechManualDrawRepository = Container::get(LtechManualDrawRepository::class);
            $manualDraw = $ltechManualDrawRepository->findForNextDraw($lotteryOrm);
            if ($manualDraw) {
                $response = $manualDraw->toLtechJson($this->ltech_bonus_name);
            } else {
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpcode !== 200) {
                    throw new Exception("Ltech response error. Code: $httpcode");
                }
            }

            curl_close($ch);

            $draws = json_decode($response);
            $final_draw = null;
            $ltech_next_draw_date = null;
            $more_than_one_draw_found = false;
            $draw_after_final_draw = null;

            $lottery_next_date_local = $lottery['next_date_local'];
            if ($lottery_next_date_local == null && isset($draws[1]) && !empty($draws[1]->numbers) && !empty($draws[1]->prizes) && !empty($draws[1]->winners)) {
                $final_draw = $draws[1];
                $lottery_next_date_local = $final_draw->date;
            }
            $lottery_next_date_local_carbon = Carbon::parse($lottery_next_date_local, $lottery['timezone']);


            foreach ($draws as $key => $draw) {
                $draw_date = Carbon::parse($draw->date, $lottery['timezone']);
                if (empty($draw->numbers) || empty($draw->prizes) || empty($draw->winners)) {
                    $draw_carbon = Carbon::parse($draw->date, $lottery['timezone']);
                    $ltech_next_draw_time = $this->get_draw_hour_from_draw_dates($draw_carbon->shortEnglishDayOfWeek);
                    $ltech_next_draw_date = Carbon::parse("{$draw->date} $ltech_next_draw_time", $lottery['timezone']);
                    continue;
                }
                if ((isset($lottery_next_date_local_carbon) && $draw_date->isSameDay($lottery_next_date_local_carbon)) || $lottery_next_date_local === null) {
                    $final_draw = $draw;
                    if ($key > 1) {
                        // If ltech draws index is higher than 1 it means that there is more than one not synced draw
                        $more_than_one_draw_found = true;
                        $draw_after_final_draw = $draws[$key - 1]->date ?? null;
                    }
                    break;
                }
            }
            // Overwrite next draw date if there is more not synced draws than one
            if ($more_than_one_draw_found && $draw_after_final_draw !== null) {
                $draw_after_final_draw_carbon = Carbon::parse($draw_after_final_draw, $lottery['timezone']);
                $ltech_next_draw_time = $this->get_draw_hour_from_draw_dates($draw_after_final_draw_carbon->shortEnglishDayOfWeek);
                $ltech_next_draw_date = Carbon::parse("$draw_after_final_draw $ltech_next_draw_time", $lottery['timezone']);
            }

            if ($ltech_next_draw_date == null) {
                throw new Exception('Error fetching draw. Ltech next draw date is null.');
            }

            if ($final_draw === null) {
                return false; // Next draw not found, we don't need to log it
            }

            $date = Carbon::parse($final_draw->date, $lottery['timezone']);
            $time = $this->get_draw_hour_from_draw_dates($date->shortEnglishDayOfWeek, true);
            $this->date = Carbon::parse("{$final_draw->date} $time", $lottery['timezone']);
            $this->date_utc = $this->date->clone()->setTimezone(new DateTimeZone("UTC"));

            // Check if prizes have changed
            $prizes_valid = $this->check_prize_slugs($lottery, $final_draw);

            if (!$prizes_valid) {
                throw new Exception("The prizes have changed in the Ltech response. Lottery: {$lottery['name']}.");
            }

            // Next draw date validation
            if ($this->validate_draw_date($lottery, $this->date) === false) {
                return false;
            }

            $this->ltech_next_draw_date = $ltech_next_draw_date;

            $this->numbers = $final_draw->numbers->main;

            if (isset($final_draw->numbers->{$this->ltech_bonus_name})) {
                $this->bonus_numbers = is_array($final_draw->numbers->{$this->ltech_bonus_name}) ?
                    $final_draw->numbers->{$this->ltech_bonus_name} :
                    [$final_draw->numbers->{$this->ltech_bonus_name}];
            }

            $this->set_additional_data($final_draw);

            // Jackpot should be taken from the nearst last draw date
            $jackpot_draw = $draws[0];
            foreach ($draws as $draw_for_jackpot) {
                if (empty($draw_for_jackpot->numbers) || empty($draw_for_jackpot->prizes) || empty($draw_for_jackpot->winners)) {
                    $jackpot_draw = $draw_for_jackpot;
                    continue;
                }
                break;
            }

            $this->jackpot = $jackpot_draw->jackpot->total / 1000000;

            $final_draw_sorted = $this->sort_final_draw($lottery, $final_draw);

            $winners_prizes = $final_draw_sorted['prizes'];
            $winners_counts = $final_draw_sorted['winners'];

            $winners_prizes = array_values($winners_prizes);
            $winners_counts = array_values($winners_counts);

            foreach ($winners_counts as $key => $value) {
                $this->prizes[] = [$value];
                if (!isset($winners_prizes[$key])) {
                    $this->prizes[$key][1] = "0.00";
                }
            }
            foreach ($winners_prizes as $key => $value) {
                $this->prizes[$key][1] = $value;
            }

            $lottorisqLogRepository->addSuccessLog(null, null, null, LottorisqLog::MESSAGE_SUCCESS_DRAW_DOWNLOAD);
        } catch (Throwable $e) {
            $lottorisqLogRepository->addLtechResponseErrorLog($e);
            return false;
        }

        if ($manualDraw) {
            $manualDraw->isProcessed = true;
            $manualDraw->save();
        }

        return true;
    }

    /**
     * Set additional data like reintegro, refund etc...
     *
     * @param object $final_draw
     *
     * @return void
     */
    protected function set_additional_data(object $final_draw): void
    {
        if (isset($final_draw->numbers->refund)) {
            $this->additional_data['refund'] = $final_draw->numbers->refund;
        }
    }

    /**
     * Get lottery prizes to compare with l-tech
     *
     * @param array $lottery
     *
     * @return array
     */
    protected function get_lottery_prizes(array $lottery): array
    {
        $type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $this->date->format('Y-m-d'));

        $type_data = Model_Lottery_Type_Data::find(
            [
                'select' => [
                    'id',
                    'match_n',
                    'match_b',
                    'additional_data'
                ],
                'where' => [
                    'lottery_type_id' => $type['id'],
                ],
                'order_by' => 'id'
            ]
        );

        foreach ($type_data as $type) {
            $slug = $this->get_ltech_slug($type->match_n, $type->match_b, $type->additional_data);
            if ($slug) {
                $type->slug = $slug;
            }
        }

        return $type_data;
    }


    /**
     * Get l-tech slug per lottery
     *
     * @param int         $match_n
     * @param int         $match_b
     * @param string|null $additional_data
     *
     * @return string
     */
    protected function get_ltech_slug(int $match_n, int $match_b, string $additional_data = null): string
    {
        $bonus = $match_b > 0 ? '-' . $match_b : '';

        return "match-{$match_n}{$bonus}";
    }


    /**
     * Sort final draw prizes as they are set in lottery_type_data
     *
     * @param array  $lottery
     * @param object $final_draw
     *
     * @return array
     */
    protected function sort_final_draw(array $lottery, object $final_draw): array
    {
        $lottery_prizes = $this->get_lottery_prizes($lottery);
        $sorted_prizes = [
            'prizes' => [],
            'winners' => []
        ];
        foreach ($lottery_prizes as $lp_key => $lottery_prize) {
            foreach ($final_draw->prizes as $fd_p_key => $final_draw_prizes_prize) {
                if ($fd_p_key == $lottery_prize['slug']) {
                    $sorted_prizes['prizes'][$lp_key] = $final_draw_prizes_prize;
                }
            }
            foreach ($final_draw->winners as $fd_w_key => $final_draw_winners_prize) {
                if ($fd_w_key == $lottery_prize['slug']) {
                    $sorted_prizes['winners'][$lp_key] = $final_draw_winners_prize;
                }
            }
        }

        return $sorted_prizes;
    }

    /**
     * Validate draw date
     *
     * @param array                         $lottery
     * @param Carbon $date
     *
     * @return bool
     * @throws Exception
     */
    protected function validate_draw_date(array $lottery, Carbon $date): bool
    {
        if (Helpers_Lottery::is_drawed_on_weekday($lottery, $date) === false) {
            throw new Exception("Draw date missmatch - bad weekday. Lottery: {$lottery['name']}. Ltech next draw date: {$date->format(Helpers_Time::DATETIME_FORMAT)}.");
        }

        return true;
    }


    /**
     * Checks if the prize slugs from the ltech response are the same as the ones from Model_Lottery_Type_Data
     *
     * @return bool
     * @var object $final_draw
     * @var array  $lottery
     */
    protected function check_prize_slugs(array $lottery, object $final_draw): bool
    {
        $lottery_prizes = $this->get_lottery_prizes($lottery);

        $lottery_prize_slugs = [];
        foreach ($lottery_prizes as $lottery_prize) {
            $lottery_prize_slugs[] = $lottery_prize->slug;
        }

        $ltech_winners = (array)$final_draw->winners;
        $ltech_winners_slugs = array_keys($ltech_winners);
        $valid_ltech_winners_slugs = array_diff($ltech_winners_slugs, $this->lottery_ltech_ignored_slugs);

        rsort($lottery_prize_slugs);
        rsort($valid_ltech_winners_slugs);

        // Check if Ltech winners slugs are same as the lottery prize slugs
        if ($lottery_prize_slugs === $valid_ltech_winners_slugs) {
            return true;
        }

        return false;
    }

    public function process_lottery(): void // TODO: {Vordis 2021-02-18 15:46:25} fix this better. Shared problem with feeds (ozlotto etc)
    {
        $this->lottery_to_update->last_date_local = $this->lottery_to_update->next_date_local;
        parent::process_lottery();
    }
}
