<?php

namespace Tests;

use Carbon\Carbon;
use Fuel\Core\Fieldset;
use Helpers_Time;
use Response_Lcs_Lottery_Draw_Data;
use Task_Lotterycentralserver_Fetch_Game;
use Task_Lotterycentralserver_Fetch_Lottery_Lottery;
use Task_Result;
use Test_Factory_Lottery;

require_once(APPPATH . "/tests/feature/task/lotterycentralserver/fetch/task.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Lottery extends Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Task
{
    protected $task_class_mockable = 'Task_Lotterycentralserver_Fetch_Lottery_Lottery_Mockable';

    protected $response_class_mockable = 'Response_Lcs_Lottery_Draw_Data_Mockable';

    protected $task_methods = ['get_lottery', 'fetch', 'get_result', 'get_last_error_message'];

    protected $mockable_classes = [
        Task_Lotterycentralserver_Fetch_Lottery_Lottery::class,
        Response_Lcs_Lottery_Draw_Data::class,
        Task_Result::class,
    ];

    public function test_can_fetch_for_up_to_date_lottery()
    {
        $response_attributes = [
            'next_draw_date' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'next_draw_datetime_localized' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'timezone' => 'UTC',
            'jackpot' => 1.234567
        ];
        $this->run_task_with_data_from_response($response_attributes);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        self::assertSame(
            (string)$result_from_task->get_data_item('draw_data_response'),
            json_encode($response_attributes)
        );
        $this->assert_success();

        self::assertEquals($result_from_task->get_result_code(), Task_Lotterycentralserver_Fetch_Game::UP_TO_DATE);
    }

    public function test_can_fetch_for_outdated_lottery()
    {
        $response_attributes = [
            'next_draw_date' => Carbon::tomorrow()->format(Helpers_Time::DATETIME_FORMAT),
            'next_draw_datetime_localized' => Carbon::tomorrow()->format(Helpers_Time::DATETIME_FORMAT),
            'timezone' => 'UTC',
            'jackpot' => 1.234567
        ];
        $this->run_task_with_data_from_response($response_attributes);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        self::assertSame(
            (string)$result_from_task->get_data_item('draw_data_response'),
            json_encode($response_attributes)
        );
        $this->assert_success();

        self::assertTrue($result_from_task->is_flag_set(Task_Lotterycentralserver_Fetch_Game::DRAW_DATE_DIFFER));
    }

    public function test_can_fetch_for_lottery_with_empty_next_draw_date()
    {
        $this->create_lottery();
        $this->lottery['next_date_local'] = null;
        $response_attributes = [
            'next_draw_date' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'next_draw_datetime_localized' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'timezone' => 'UTC',
            'jackpot' => 1.234567
        ];
        $this->run_task_with_data_from_response($response_attributes);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        self::assertSame(
            (string)$result_from_task->get_data_item('draw_data_response'),
            json_encode($response_attributes)
        );
        $this->assert_success();

        self::assertTrue($result_from_task->is_flag_set(Task_Lotterycentralserver_Fetch_Game::DRAW_DATE_DIFFER));
    }

    public function test_can_fetch_for_lottery_with_new_jackpot()
    {
        $response_attributes = [
            'next_draw_date' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'next_draw_datetime_localized' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'timezone' => 'UTC',
            'jackpot' => 2.34567
        ];
        $this->run_task_with_data_from_response($response_attributes);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        self::assertSame(
            (string)$result_from_task->get_data_item('draw_data_response'),
            json_encode($response_attributes)
        );
        $this->assert_success();

        self::assertTrue($result_from_task->is_flag_set(Task_Lotterycentralserver_Fetch_Game::JACKPOT_DIFFER));
    }
}