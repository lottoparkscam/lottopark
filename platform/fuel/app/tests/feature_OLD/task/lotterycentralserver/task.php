<?php

namespace Tests;

use Carbon\Carbon;
use Helpers_Time;
use Task_Lotterycentralserver_Fetch_Lottery_Lottery;
use Test_Factory_Lottery;

abstract class Test_Feature_Classes_Task_Lotterycentralserver_Task extends \Test_Mock_Feature
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Task_Lotterycentralserver_Task
     */
    protected $task_stub;

    /**
     * @var \Model_Lottery
     */
    protected $lottery;
    protected $now;
    protected $task_class_mockable;
    protected $task_methods = [];
    protected $result;
    protected $in_transaction = true;

    public function get_task_stub()
    {
        if (is_null($this->task_stub)) {
            throw new \Exception("Task has not been stubbed yet.");
        }

        return $this->task_stub;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::now();
        $this->stub_task_class();
    }

    protected function assert_success(): void
    {
        self::assertEmpty($this->task_stub->get_last_error_message());
        self::assertTrue($this->task_stub->get_result()->is_successful());
    }

    protected function stub_task_class(): void
    {
        $this->task_stub = $this->getMockBuilder($this->task_class_mockable)
            ->disableOriginalConstructor()
            ->onlyMethods($this->task_methods)
            ->getMock();
        $this->result = $this->getMockBuilder('Task_Result_Mockable')
            ->onlyMethods([])
            ->getMock();
        $this->task_stub->method('get_result')
            ->willReturn($this->result);
    }

    protected function create_lottery(array $values = []): void
    {
        $default_values = [
            'last_update' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'next_date_local' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'current_jackpot' => 1.234567,
            'timezone' => 'UTC',
        ];
        $this->lottery = \Model_Lottery::forge(
            Test_Factory_Lottery::create($default_values + $values)->get_result()['lottery'][0]
        );
        $this->task_stub->method('get_lottery')
            ->willReturn($this->lottery);
    }
}