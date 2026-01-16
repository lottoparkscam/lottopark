<?php

namespace Tests;

use Carbon\Carbon;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Query_Builder_Insert;
use Fuel\Core\DB;
use Helper_Lottery;
use Helpers_Time;
use Model_Lottery_Type;
use Model_Lottery_Type_Multiplier;
use Response_Lcs_Lottery_Last_Draws;
use Task_Lotterycentralserver_Database_Insert_Prizes;
use Task_Result;

require_once(APPPATH . "/tests/feature/task/lotterycentralserver/database/task.php");
require_once(APPPATH . "/tests/feature_OLD/task/lotterycentralserver/database/insert/draw.php");
require_once(APPPATH . "/tests/feature/task/lotterycentralserver/fetch/lottery/draw.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Database_Insert_Prizes extends Test_Feature_Classes_Task_Lotterycentralserver_Database_Task
{
    protected $task_class_mockable = 'Task_Lotterycentralserver_Database_Insert_Prizes_Mockable';

    protected $task_methods = [
        'get_result',
        'get_last_error_message',
        'get_previous_task_result',
    ];

    protected $query_builder_mock_class = 'Database_Query_Builder_Insert_Mockable';

    protected $draw_fetch_result;

    protected $draw_insert_result;

    protected $mockable_classes = [
        Task_Lotterycentralserver_Database_Insert_Prizes::class,
        Task_Result::class,
        Database_Query_Builder_Insert::class,
    ];

    protected function prepare_for_draw_and_multipliers(array $draw_data, array $multipliers = [])
    {
        $fetch_lottery_draw_test = new Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Draw;
        $fetch_lottery_draw_test->setUp();
        $insert_draws_test = new Test_Feature_Classes_Task_Lotterycentralserver_Database_Insert_Draw;
        $insert_draws_test->setUp();
        $lottery_fetch_result = new Task_Result();
        $lottery_fetch_result->set_flag(\Task_Lotterycentralserver_Fetch_Lottery_Lottery::DRAW_DATE_DIFFER);
        $fetch_lottery_draw_test->run_task_with_data_from_response($draw_data);
        $this->draw_fetch_result = $fetch_lottery_draw_test->get_task_stub()->get_result();
        $insert_draws_test->insert_draws($this->draw_fetch_result->get_data_item('last_draws'));
        $this->draw_insert_result = $insert_draws_test->get_task_stub()->get_result();
        $this->task_stub->set_multipliers($multipliers);
        $this->task_stub->set_previous_task_result($this->draw_fetch_result);
        $this->task_stub->set_lottery_draws($this->draw_insert_result->get_data_item('draws'));
    }

    public function test_insert_prizes_keno()
    {
        // Keno does not insert any prizes and as such it does not use this task either
        // We should check if the proper exception is being thrown if by any chance the task is executed
        $this->prepare_for_draw_and_multipliers(Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Draw::KENO_ONE_DRAW);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The draw being inserted has no prize data.");
        $this->task_stub->run();
    }

}
