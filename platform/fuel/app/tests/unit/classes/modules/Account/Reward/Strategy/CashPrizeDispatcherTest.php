<?php

namespace Unit\Classes\Modules\Account\Reward\Strategy;

use Closure;
use Modules\Account\Balance\BalanceContract;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\Strategy\CashPrizeDispatcher;
use RuntimeException;
use Test_Unit;

class CashPrizeDispatcherTest extends Test_Unit
{
    private BalanceContract $balance;
    private CashPrizeDispatcher $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->balance = $this->createMock(BalanceContract::class);
        $this->service = new CashPrizeDispatcher($this->balance);
    }

    /** @test */
    public function dispatchPrize__is_not_cash_type__skips(): void
    {
        // Given
        $line = $this->get_line();

        $this->balance
            ->expects($this->never())
            ->method('increase');

        // When
        $this->service->dispatchPrize($line);
    }

    /**
     * @test
     * @dataProvider user_group_prize_payout_percent_provider
     * @param Closure $user_closure
     */
    public function dispatchPrize__valid_with_prize_deduction__success(Closure $user_closure): void
    {
        // Given
        $user = $user_closure();
        $line = $this->get_line(PrizeType::CASH, $user);
        $line->prize = 10;
        $expected_prize_after_deduction = $line->prize;
        if (!empty($user->group)) {
            $payout_as_percent = (float)($user->group->prize_payout_percent / 100);
            $expected_prize_after_deduction = $line->prize * $payout_as_percent;
        }

        $this->balance
            ->expects($this->once())
            ->method('increase')
            ->with(
                $line->ticket->user->id,
                $expected_prize_after_deduction,
                $line->ticket->user->currency->code
            );

        // When
        $this->service->dispatchPrize($line);
        $this->service->dispatch();
    }

    /** @test */
    public function dispatch__not_enqueued__throws_runtime_exception(): void
    {
        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Class <Modules\Account\Reward\Strategy\CashPrizeDispatcher> can not be dispatched until it is not enqueued'
        );

        // When
        $this->service->dispatch();
    }

    /** @test */
    public function enqueued__not_dispatched__shows_notice_in_testing_env(): void
    {
        ob_start();

        // Given
        $line = $this->get_line(PrizeType::CASH);
        $line->prize = 10;
        $expectedNotice = 'Class <Modules\Account\Reward\Strategy\CashPrizeDispatcher> has pending tasks but it was never dispatched';

        // When
        $this->service->dispatchPrize($line);
        $this->service->__destruct();

        // Then
        $actual = ob_get_contents();
        $this->assertSame($expectedNotice, $actual);

        ob_end_flush();
    }

    public function user_group_prize_payout_percent_provider(): array
    {
        return [
            'no user group, pays 100% of prize' => [function () {
                $user = $this->get_user();
                unset($user->group);
                return $user;
            }],

            'pays 90% of prize' => [function () {
                $user = $this->get_user();
                $user->group->prize_payout_percent = 90;
                return $user;
            }],

            'pays 25% of prize' => [function () {
                $user = $this->get_user();
                $user->group->prize_payout_percent = 25;
                return $user;
            }],

        ];
    }
}
