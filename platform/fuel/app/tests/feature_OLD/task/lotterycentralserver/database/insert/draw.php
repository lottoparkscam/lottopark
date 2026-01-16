<?php

namespace Tests;

use Carbon\Carbon;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Query_Builder_Insert;
use Fuel\Core\DB;
use Helper_Lottery;
use Helpers_Time;
use Task_Lotterycentralserver_Database_Insert_Draw;
use Task_Result;

//require_once(APPPATH . "/tests/feature_OLD/task/lotterycentralserver/database/task.php");
//require_once(APPPATH . "/tests/feature/task/lotterycentralserver/fetch/lottery/draw.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Database_Insert_Draw extends Test_Feature_Classes_Task_Lotterycentralserver_Database_Task
{
    protected $task_class_mockable = 'Task_Lotterycentralserver_Database_Insert_Draw_Mockable';

    protected $task_methods = [
        'get_result',
        'get_lottery',
        'get_last_error_message',
        'get_previous_task_result',
        'get_lottery_types',
        'get_date_download'
    ];

    protected $query_builder_mock_class = 'Database_Query_Builder_Insert_Mockable';

    protected $lottery_type;

    protected $date_download_mocked;

    protected $mockable_classes = [
        Task_Lotterycentralserver_Database_Insert_Draw::class,
        Task_Result::class,
        Database_Query_Builder_Insert::class,
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->date_download_mocked = Helpers_Time::now();
        $this->task_stub->method('get_date_download')->willReturn($this->date_download_mocked);
        $this->lottery_type = [
            'id' => 1,
            'date_start' => Carbon::now()->subYear()->format(\Helpers_Time::DATETIME_FORMAT),
            'date_end' => null
        ];
        $this->task_stub->method('get_lottery_types')->willReturn([$this->lottery_type]);
    }

    protected function expected_records_from_draws(array $draws): array
    {
        $records = [];
        foreach ($draws as $id => $draw) {
            $record = [
                $this->lottery->id,
                $this->lottery_type['id'],
                $draw['draw_no'],
                $this->date_download_mocked,
                $draw['date'],
                implode(",", $draw['numbers'][0]),
                empty($draw['numbers'][1]) ? null : implode(",", $draw['numbers'][1]),
                $draw['prize_total'],
                $draw['lines_won_count'],
                empty($draw['lottery_prizes']) ? 0 : array_values($draw['lottery_prizes'])[0]['total'], // top prize value, if not won will be 0 (LCS)
                Helper_Lottery::calculate_jackpot_value($draw['jackpot'])
            ];
            $records[] = $record;
        }

        return $records;
    }

    protected function prepare_expected_query(array $values = []): Database_Query_Builder
    {
        $query = DB::insert('lottery_draw');
        $query->columns([
            'lottery_id',
            'lottery_type_id',
            'draw_no',
            'date_download',
            'date_local',
            'numbers',
            'bnumbers',
            'total_prize',
            'total_winners',
            'final_jackpot',
            'jackpot'
        ]);
        $query->values($values);

        return $query;
    }

    public function insert_draws(array $draws)
    {
        $this->previous_task_result->set_data(['last_draws' => $draws]);
        if (is_null($this->lottery)) {
            $this->create_lottery();
        }
        $this->task_stub->run();
    }

    protected function insert_draws_and_assert(array $draws)
    {
        $this->insert_draws($draws);
        /** @var Task_Result $result */
        $result = $this->result;
        $this->assertIsArray($result->get_data_item('draws'));
        $this->assertEquals(count($result->get_data_item('draws')), count($draws));
        $expected_records = $this->expected_records_from_draws($draws);
        $expected_query = $this->prepare_expected_query($expected_records);
        $expected_query_compiled = $expected_query->compile();
        $actual_query = $this->task_stub->get_query();
        $actual_query_compiled = $actual_query->compile();
        $this->assertSame($expected_query_compiled, $actual_query_compiled);
    }

    public function test_insert_one_draw_keno()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->insert_draws_and_assert(Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Draw::KENO_ONE_DRAW['draws']);
    }

    public function test_insert_many_draws_keno()
    {
        $this->create_lottery(['type' => 'keno']);
        $this->insert_draws_and_assert(Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Lottery_Draw::KENO_MANY_DRAWS['draws']);
    }
}
