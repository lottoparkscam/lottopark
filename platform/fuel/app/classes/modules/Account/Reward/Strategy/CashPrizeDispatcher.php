<?php

namespace Modules\Account\Reward\Strategy;

use Models\WhitelabelUser;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Balance\BalanceContract;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\RewardDispatchingStrategyContract;
use Services\Shared\AbstractDispatchAble;
use Services\Shared\DispatchAble;

/**
 * Class CashPrizeDispatcher
 */
class CashPrizeDispatcher extends AbstractDispatchAble implements RewardDispatchingStrategyContract, DispatchAble
{
    private BalanceContract $balance;

    public function __construct(BalanceContract $balance)
    {
        $this->balance = $balance;
    }

    public function dispatchPrize(WhitelabelRaffleTicketLine $line): void
    {
        if ($isNotCashType = $line->prizeType()->notEquals(PrizeType::CASH())) {
            return;
        }

        $user = $line->ticket->user;
        $percent = $this->getUserPayoutPercent($user);
        $prizeAfterDeduction = $line->prize * $percent;

        if ($prizeAfterDeduction > 0) {
            $this->balance->increase($user->id, $prizeAfterDeduction, $user->currency->code);
            $this->enqueue();
        }
    }

    private function getUserPayoutPercent(WhitelabelUser $user): float
    {
        if (empty($user->group)) {
            return 1.0;
        }
        return (float)($user->group->prize_payout_percent / 100); # for example 0.9, 0.75 etc
    }

    protected function enqueue(...$args): void
    {
        $this->set_task(function () {
            $this->balance->dispatch();
        });
    }
}
