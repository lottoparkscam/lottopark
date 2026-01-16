<?php

use Fuel\Core\Cache;
use Helpers\ArrayHelper;
use Repositories\LotteryRepository;
use Repositories\LotteryLogRepository;

/** Chain of tasks, which will update draw data in whitelotto: lottery, draw, prizes. */
final class Task_Lotterycentralserver_Chain_Update_Draw extends Task_Lotterycentralserver_Chain_Task
{

    protected $in_transaction = true;

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        // find model
        $lottery_model = Model_Lottery::find_one_by('slug', $this->lottery_slug);

        // throw if model not found
        if (!($lottery_model instanceof Model_Lottery)) { // NOTE: additional brackets for readability.
            throw new \Exception('API request has not been done. Model not found. Failed to load lottery - ' . $this->lottery_slug);
        }

        // exit early if lottery is disabled
        if ($lottery_model->is_disabled()) {
            return;
        }

        // we have valid lottery model, fetch lottery data
        $lottery_fetch_result = Task_Lotterycentralserver_Fetch_Lottery_Lottery::execute($lottery_model);
        // throw failure if task failed
        $this->throw_task_failure($lottery_fetch_result, 'Failed to fetch lottery data from LCS');
        /** @var Response_Lcs_Lottery_Draw_Data $draw_data_response */
        $draw_data_response = &$lottery_fetch_result->get_data()['draw_data_response'];
        
        // success switch over results
        // NOTE: I'm omitting default with premeditation, there should be 0 possibility that successful task return outside of range
        switch ($lottery_fetch_result->get_result_code()) {
            case Task_Lotterycentralserver_Fetch_Game::UP_TO_DATE:
                Helpers_Cli::writeln("Lottery fetch result = UP_TO_DATE, only save the lottery");
                $lottery_model->save();

                return; // date is up to date - update last_updated (it means that lottery was checked).
            case Task_Lotterycentralserver_Fetch_Game::JACKPOT_DIFFER: // NOTE: this works as ONLY jackpot flag is set.
                Helpers_Cli::writeln("Lottery fetch result = JACKPOT_DIFFER, update lottery jackpot");
                $lottery_update_result = Task_Lotterycentralserver_Database_Update_Lottery::execute($lottery_model, $lottery_fetch_result);
                $this->throw_task_failure($lottery_update_result, 'Failed to update jackpot');
                // log success of update jackpot
                $new_jackpot_lcs = $draw_data_response->jackpot;

                $lotteryLogRepository->addSuccessLog(
                    $lottery_model->id,
                    "Successfully updated jackpot, new value (lcs) = $new_jackpot_lcs"
                );

                return;
        }
        
        $draw_fetch_result = Task_Lotterycentralserver_Fetch_Lottery_Draw::execute($lottery_model, $lottery_fetch_result);
        $last_draw = $draw_fetch_result->get_data()['last_draws'];

        // throw failure if task failed
        $this->throw_task_failure($draw_fetch_result, 'Failed to fetch draw data from LCS');

        // check result of the task
        Helpers_Cli::writeln("Draw fetch result code: " . $draw_fetch_result->get_result_code());
        if ($draw_fetch_result->get_result_code() === Task_Lotterycentralserver_Fetch_Lottery_Draw::UP_TO_DATE) {
            Helpers_Cli::success("Lottery is up to date");

            return; // there is no need for further action, we have last draw (or it's not calculated yet)
        }

        $draw_insert_result = Task_Lotterycentralserver_Database_Insert_Draw::execute($lottery_model, $draw_fetch_result);
        $this->throw_task_failure($draw_insert_result, 'Failed to update draw');

        $ticket_multipliers = Model_Lottery_Type_Multiplier::for_ticket_saving();

        // draw is in database, now insert it's prizes
        if ($lottery_model->is_keno() === false) { // Keno has static prizes
            $prizes_insert_result = Task_Lotterycentralserver_Database_Insert_Prizes::execute(
                $draw_insert_result->get_data_item('draws'),
                $draw_fetch_result,
                $ticket_multipliers[$lottery_model->id] ?? []
            );
            $this->throw_task_failure($prizes_insert_result, 'Failed to update prizes');
        }

        // last draw differ between whitelotto and LCS
        // so we need to update lottery draw data, insert draw and prizes
        $lottery_update_result = Task_Lotterycentralserver_Database_Update_Lottery::execute($lottery_model, $lottery_fetch_result);
        $this->throw_task_failure($lottery_update_result, 'Failed to update lottery draw data');
        Helpers_Cache::reset_models(Model_Lottery::class, Model_Whitelabel_Lottery::class);
        Cache::delete(LotteryRepository::CACHE_KEY_GG_WORLD_LOTTERIES_SELECTED);
        $last_draw = ArrayHelper::last($draw_insert_result->get_data_item('draws'));
        $draw_date_time = $last_draw->date_local;
        $lotteryLogRepository->addSuccessLog(
            $lottery_model->id,
            "Successfully updated lottery. Draw date time = $draw_date_time"
        );
    }
}
