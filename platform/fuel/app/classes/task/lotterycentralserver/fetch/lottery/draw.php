<?php

use Carbon\Carbon;

class Task_Lotterycentralserver_Fetch_Lottery_Draw extends Task_Lotterycentralserver_Fetch_Draw
{
    /**
     * @var Task_Result
     */
    protected $lottery_fetch_result;

    protected function __construct(Model_Lottery $lottery, Task_Result $lottery_fetch_result)
    {
        parent::__construct();
        $this->set_lottery($lottery);
        $this->set_lottery_fetch_result($lottery_fetch_result);
        $this->get_result()->set_result_code(self::UP_TO_DATE);
    }

    public function set_lottery_fetch_result(Task_Result $lottery_fetch_result)
    {
        $this->lottery_fetch_result = $lottery_fetch_result;
    }

    public function get_lottery_fetch_result(): Task_Result
    {
        return $this->lottery_fetch_result;
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        // get draw data from lcs
        Helpers_Cli::info("Fetching last draws");
        $last_draw_local = Model_Lottery_Draw::last_for_lottery_by_draw_no($this->get_lottery()->id);
        $limit = 1;
        if ($this->lottery->is_keno()) {
            $limit = 10;
        }
        if ($last_draw_local === null){
            $last_draw_local = Model_Lottery_Draw::forge(['draw_no' => 0]);
        }
        Helpers_Cli::writeln("Last draw_no: " . $last_draw_local['draw_no']);
        $last_draws = $this->fetch($limit, $last_draw_local)->draws;
        $this->evaluate_response($last_draws);
        $this->get_result()->set_data(['last_draws' => $last_draws]);

        $lcsReturnedDraws = !empty($last_draws);
        if ($lcsReturnedDraws) {
            Helpers_Cli::success("Draws array from LCS has draws");
            $this->get_result()->set_result_code(self::OUTDATED);
            foreach ($last_draws as $draw_key => $draw) {
                if (isset($draw['is_calculated']) === false) {
                    throw new \Exception("Request to LCS failed, cause: is_calculated not found in the draw (draw_no=" . $draw['draw_no'] . ")");
                }
                if (isset($last_draw_local['draw_no']) && isset($draw['draw_no']) && $last_draw_local['draw_no'] === $draw['draw_no']) {
                    Helpers_Cli::writeln("Found a draw we already have in db");
                    unset($last_draws[$draw_key]);
                    continue;
                }
                Helpers_Cli::writeln("Found a draw we can insert into database");
            }

            return;
        }

        // we know for sure that at this point lottery_fetch_result must be DRAW_DATE_DIFFER
        // so it mean that lcs probably did not calculate draw yet (published)
        // if we reached here up to date status is good - we will omit action during this task progression
        // we shall throw exception after 1h passed from due date
        $nowLocalized = Carbon::now($this->lottery->timezone);
        $drawIsLongOverdue = Carbon::parse($this->lottery->next_date_local, $this->lottery->timezone)->diffInHours($nowLocalized) > 1;
        if ($drawIsLongOverdue) {
            throw new \Exception("Draw is long overdue and we still cannot pull it from lcs.");
        }
    }
}