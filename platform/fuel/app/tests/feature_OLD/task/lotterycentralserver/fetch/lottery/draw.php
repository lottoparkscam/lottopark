<?php


namespace Tests;

use Response_Lcs_Lottery_Last_Draws;
use Task_Lotterycentralserver_Fetch_Lottery_Draw;
use Task_Result;

require_once(APPPATH . "/tests/feature/task/lotterycentralserver/fetch/task.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Draw extends Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Task
{
    protected $lottery_fetch_result;

    protected $task_class_mockable = 'Task_Lotterycentralserver_Fetch_Lottery_Draw_Mockable';

    protected $response_class_mockable = 'Response_Lcs_Lottery_Last_Draws_Mockable';

    protected $task_methods = ['get_lottery', 'fetch', 'get_result', 'get_last_error_message', 'evaluate_response', 'get_lottery_fetch_result'];

    protected $mockable_classes = [
        Task_Lotterycentralserver_Fetch_Lottery_Draw::class,
        Response_Lcs_Lottery_Last_Draws::class,
        Task_Result::class,
    ];
    /**
     * @var mixed
     */
    private $last_draws;

    public function setUp(): void
    {
        parent::setUp();
        $this->lottery_fetch_result = new Task_Result();
        $this->task_stub->method('get_lottery_fetch_result')->willReturn($this->lottery_fetch_result);
    }

    /**
     *  LCS Responses
     */
    const KENO_ONE_DRAW = [
        'draws' => [
            [
                "draw_no" => 1,
                "date" => "2021-01-12 08:45:00",
                "exact_start" => "2021-01-10 08:46:00",
                "exact_end" => "2021-01-12 08:46:00",
                "token" => null,
                "numbers" => [[19, 55, 41, 10, 60, 33, 32, 58, 9, 12, 14, 64, 39, 18, 13, 48, 5, 25, 42, 70]],
                "is_calculated" => 1,
                "report_sent" => 0,
                "sale_sum" => "4.00",
                "sale_sum_predicted" => "2.00",
                "jackpot" => "1000000.00",
                "prize_total" => "0.00",
                "currency_code" => "USD",
                "lines_won_count" => 0,
                "tickets_count" => 1,
                "lines_count" => 1,
                "transaction_hash" => null,
                "callback_transaction_hash" => null,
                "contract_address" => null,
                "hash" => null,
                "hash_algorithm" => null,
                "salt" => null
            ]
        ]
    ];

    const KENO_MANY_DRAWS = [
        'draws' => [
            [
                "draw_no" => 1,
                "date" => "2021-01-12 08:45:00",
                "exact_start" => "2021-01-12 08:46:00",
                "exact_end" => "2021-01-12 08:46:00",
                "token" => null,
                "numbers" => [[19, 55, 41, 10, 60, 33, 32, 58, 9, 12, 14, 64, 39, 18, 13, 48, 5, 25, 42, 70]],
                "is_calculated" => 1,
                "report_sent" => 0,
                "sale_sum" => "4.00",
                "sale_sum_predicted" => "2.00",
                "jackpot" => "1000000.00",
                "prize_total" => "0.00",
                "currency_code" => "USD",
                "lines_won_count" => 0,
                "tickets_count" => 1,
                "lines_count" => 1,
                "transaction_hash" => null,
                "callback_transaction_hash" => null,
                "contract_address" => null,
                "hash" => null,
                "hash_algorithm" => null,
                "salt" => null
            ],
            [
                "draw_no" => 2,
                "date" => "2021-01-13 08:45:00",
                "exact_start" => "2021-01-13 08:46:00",
                "exact_end" => "2021-01-13 08:46:00",
                "token" => null,
                "numbers" => [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]],
                "is_calculated" => 1,
                "report_sent" => 0,
                "sale_sum" => "0.00",
                "sale_sum_predicted" => "0.00",
                "jackpot" => "1000000.00",
                "prize_total" => "0.00",
                "currency_code" => "USD",
                "lines_won_count" => 0,
                "tickets_count" => 0,
                "lines_count" => 0,
                "transaction_hash" => null,
                "callback_transaction_hash" => null,
                "contract_address" => null,
                "hash" => null,
                "hash_algorithm" => null,
                "salt" => null
            ]
        ]
    ];

    const KENO_EMPTY_DRAWS = [
        'draws' => []
    ];

    public function assert_success(): void
    {
        parent::assert_success();
        $this->assertIsArray($this->last_draws);
    }

    public function test_keno_success_no_draws()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->lottery_fetch_result->set_flag(\Task_Lotterycentralserver_Fetch_Lottery_Lottery::DRAW_DATE_DIFFER);
        $this->run_task_with_data_from_response(self::KENO_EMPTY_DRAWS);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        $this->last_draws = $result_from_task->get_data_item('last_draws');
        $this->assert_success();
        $this->assertEmpty($this->last_draws);
        $this->assertTrue($result_from_task->is_flag_set(\Task_Lotterycentralserver_Fetch_Draw::OUTDATED));
    }

    public function test_keno_up_to_date()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->lottery_fetch_result->set_flag(\Task_Lotterycentralserver_Fetch_Lottery_Lottery::UP_TO_DATE);
        $this->run_task_with_data_from_response(self::KENO_EMPTY_DRAWS);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        $this->last_draws = $result_from_task->get_data_item('last_draws');
        $this->assert_success();
        $this->assertEmpty($this->last_draws);
        $this->assertEquals(\Task_Lotterycentralserver_Fetch_Draw::UP_TO_DATE, $result_from_task->get_result_code());
    }

    public function test_keno_success_one_draw()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->lottery_fetch_result->set_flag(\Task_Lotterycentralserver_Fetch_Lottery_Lottery::DRAW_DATE_DIFFER);
        $this->run_task_with_data_from_response(self::KENO_ONE_DRAW);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        $this->last_draws = $result_from_task->get_data_item('last_draws');
        $this->assert_success();
        $this->assertSame(count($this->last_draws), 1);
        $this->assertSame(
            $this->last_draws,
            self::KENO_ONE_DRAW['draws']
        );
        $this->assertTrue($result_from_task->is_flag_set(\Task_Lotterycentralserver_Fetch_Draw::OUTDATED));
    }

    public function test_keno_success_many_draws()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->lottery_fetch_result->set_flag(\Task_Lotterycentralserver_Fetch_Lottery_Lottery::DRAW_DATE_DIFFER);
        $this->run_task_with_data_from_response(self::KENO_MANY_DRAWS);
        /** @var Task_Result $result_from_task */
        $result_from_task = $this->task_stub->get_result();
        $this->last_draws = $result_from_task->get_data_item('last_draws');
        $this->assert_success();
        $this->assertSame(count($this->last_draws), 2);
        $this->assertSame(
            $this->last_draws,
            self::KENO_MANY_DRAWS['draws']
        );
        $this->assertTrue($result_from_task->is_flag_set(\Task_Lotterycentralserver_Fetch_Draw::OUTDATED));
    }

}