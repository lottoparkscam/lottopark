<?php

use Carbon\Carbon;

/**
 * Update draw data for lottery, data will be fetched from Lottery Central Server.
 */
final class Task_Lotterycentralserver_Database_Update_Lottery extends Task_Lotterycentralserver_Database_Lottery
{
    /**
     * Calculate jackpot from local value to usd.
     * // TODO: {Vordis 2019-06-13 13:24:20} this probably should go into model trait
     *
     * @param string $jackpot new jackpot value, received from LCS.
     *
     * @return float jackpot in usd.
     * @throws Exception if failed to find currency model.
     */
    private function calculate_jackpot_in_usd(string $jackpot): float
    {
        // get rate from currency table
        // find model 
        $currency = Model_Currency::find_by_pk($this->lottery->currency_id);
        // throw if model not found
        if (!($currency instanceof Model_Currency)) { // NOTE: additional brackets for readability.
            throw new Exception('Failed to find currency, lottery - '
                . $this->lottery->slug . ', currency_id - ' . $this->lottery->currency_id);
        }

        return round((float)$jackpot / $currency->rate, 8);
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     * @throws Throwable rethrows Carbon exceptions
     */
    public function run(): void
    {
        Helpers_Cli::info("Updating the lottery");
        /**
         * @var Response_Lcs_Lottery_Draw_Data $draw_data_response
         */
        $draw_data_response = &$this->previous_task_result->get_data()['draw_data_response'];

        if ($this->previous_task_result->is_flag_set(Task_Lotterycentralserver_Fetch_Lottery_Lottery::DRAW_DATE_DIFFER)) {
            Helpers_Cli::writeln("DRAW_DATE_DIFFER is set so we update lottery data with latest draws data");
            $last_draw = Model_Lottery_Draw::last_for_lottery_by_draw_no($this->lottery->id);
            if ($last_draw === null){
                throw new Exception("Cannot fetch last draw from database");
            }
            $next_draw_date = Carbon::parse($draw_data_response->next_draw_datetime_localized['date']
                . " "
                . $draw_data_response->next_draw_datetime_localized['time'], $this->lottery->timezone);
            $this->lottery->set(
                [
                    // dates
                    'last_date_local' => $last_draw->date_local,
                    'next_date_local' => $next_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                    'next_date_utc' => $next_draw_date->setTimezone('UTC')->format(Helpers_Time::DATETIME_FORMAT),
                    // draw data
                    'last_numbers' => $last_draw['numbers'],
                    'last_bnumbers' => $last_draw['bnumbers'] ?? null,
                    'last_total_prize' => $last_draw['total_prize'],
                    'last_total_winners' => $last_draw['total_winners'],
                    // jackpot_prize = total for top prize (will be 0 if not won)
                    'last_jackpot_prize' => $last_draw['final_jackpot'],
                ]
            );
        }

        if ($this->previous_task_result->is_flag_set(Task_Lotterycentralserver_Fetch_Lottery_Lottery::JACKPOT_DIFFER)) {
            Helpers_Cli::writeln("JACKPOT_DIFFER is set so we update the jackpot");
            $jackpot = $draw_data_response->jackpot;
            $this->lottery
                ->set_current_jackpot($jackpot)
                ->set_current_jackpot_usd(
                    (string)$this->calculate_jackpot_in_usd($jackpot)
                );
        }

        $this->lottery->save();
        Lotto_Helper::clear_cache(['model_lottery']);
    }
}
