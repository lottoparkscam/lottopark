<?php

namespace Tests\Unit\Task\LotteryCentralServer;

use Carbon\Carbon;
use Test_Unit;
use Task_Lotterycentralserver_Task;

final class TaskTest extends Test_Unit
{
    private Task_Lotterycentralserver_Task $lcsTask;

    public function setUp(): void
    {
        parent::setUp();
        $this->lcsTask = $this->getMockForAbstractClass(
            Task_Lotterycentralserver_Task::class,
            [],
            '',
            false
        );
    }


    /**
     * @test
     * @dataProvider providerTestCases
     */
    public function shouldAddInsufficientBalanceLog(string $carbon, bool $expected): void
    {
        Carbon::setTestNow($carbon);
        $actual = $this->lcsTask::shouldAddInsufficientBalanceLog();
        Carbon::setTestNow(false);
        $this->assertSame($expected, $actual);
    }

    public static function providerTestCases(): array
    {
        return [
            ['2022-05-20 13:01:00', true],
            ['2022-05-20 13:00:00', true],
            ['2022-05-20 13:02:00', false],
            ['2022-05-20 13:07:00', false],
            ['2022-05-20 13:59:00', false],
            ['2022-05-20 12:57:00', false],
        ];
    }
}
