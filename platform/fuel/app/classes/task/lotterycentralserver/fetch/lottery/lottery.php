<?php

use Carbon\Carbon;
use Repositories\LotteryLogRepository;

/**
 * Fetch data for lcs lottery, also check it against model and establish if update is needed.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 07:38:22
 */
final class Task_Lotterycentralserver_Fetch_Lottery_Lottery extends Task_Lotterycentralserver_Fetch_Game
{
    protected function __construct(Model_Lottery $lottery)
    {
        parent::__construct();
        $this->set_lottery($lottery);
    }

    /**
     *
     * @return \Response_Lcs_Lottery_Draw_Data
     */
    public function fetch(): Response_Interface
    {
        // prepare necessary parameters for communication
        $route = "lottery/draw_data";
        $endpoint_url = parent::absolute_url($route);
        $headers = parent::headers($route, Helpers_Lottery::get_slug($this->get_lottery()->id));

        // query LCS via Curl and return decoded result
        $response = Services_Curl::get_json($endpoint_url, $headers);

        return Response_Lcs_Lottery_Draw_Data::build_from_json($response);
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        $this->get_result()->set_result_code(self::UP_TO_DATE);
        // get draw data from lcs
        Helpers_Cli::info("Fetching draw data from LCS");
        $draw_data_response = $this->fetch();
        if ($draw_data_response->is_invalid()) {
            throw new \Exception($draw_data_response->get_full_error_message(), $draw_data_response->get_status_code());
        }
        $draw_data_response->define_additional_fields($this->get_lottery()->timezone);

        // set result
        $this->get_result()->set_data(
            [
                'draw_data_response' => $draw_data_response
            ]
        );
        Helpers_Cli::writeln("LCS responded: " . $draw_data_response);

        // NOTE: default code is 0, so it's safe for bitwise operations.
        // check if fetched draw date is different and set appropriate code
        $new_draw_date = Carbon::createFromTimeString(
            $draw_data_response->next_draw_datetime_localized['date']
            . " "
            . $draw_data_response->next_draw_datetime_localized['time'], $this->lottery->timezone
        );
        $next_date_local = $this->get_lottery()->next_date_local ?? null;
        if (empty($next_date_local) === false) {
            $next_date_local = Carbon::parse($next_date_local, $this->lottery->timezone);
            if ($new_draw_date->notEqualTo($next_date_local)) {
                $this->get_result()->set_flag(self::DRAW_DATE_DIFFER);
                Helpers_Cli::info( "Setting flag DRAW_DATE_DIFFER - next_date_local not empty and new_draw_date !== next_date_local");
            }
        } else {
            Helpers_Cli::info( "Setting flag DRAW_DATE_DIFFER - next_date_local is empty");
            $this->get_result()->set_flag(self::DRAW_DATE_DIFFER);
        }

        // check if jackpot was changed and set appropriate code
        $current_jackpot = $this->get_lottery()->current_jackpot;
        $is_jackpot_changed = $current_jackpot != Helper_Lottery::calculate_jackpot_value($draw_data_response->jackpot);
        // NOTE: not strict above with premeditation - I will not write additional functionality to format number to have trailing zeros
        // just for comparison's sake
        if ($is_jackpot_changed) {
            Helpers_Cli::info("Setting flag JACKPOT_DIFFER");
            $this->get_result()->set_flag(self::JACKPOT_DIFFER);
        }

        // otherwise end with default (up to date)
    }

    protected function on_execution_failure(\Throwable $throwable, bool $shouldLogError = true): void
    {
        $lotteryId = $this->get_lottery()['id'];

        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);
        $isServiceUnavailableError = $throwable->getCode() === 503;

        // Do not log error when it is 503 response and last success log was in the last hour
        $shouldLogError = true;
        if ($isServiceUnavailableError) {
            $shouldLogError = $lotteryLogRepository->successLogNotExistsInTheLastHour($lotteryId);
            global $shouldLogWhileLcsIsUnavailable;
            $shouldLogWhileLcsIsUnavailable = $shouldLogError;
        }

        parent::on_execution_failure($throwable, $shouldLogError);
    }
}
