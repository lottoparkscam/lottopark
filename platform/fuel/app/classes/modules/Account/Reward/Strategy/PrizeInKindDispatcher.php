<?php

namespace Modules\Account\Reward\Strategy;

use Models\RaffleRuleTierInKindPrize;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\RewardDispatchingStrategyContract;
use Modules\Account\UserPopupQueueDecorator;

class PrizeInKindDispatcher implements RewardDispatchingStrategyContract
{
    private UserPopupQueueDecorator $queuePopup;

    public function __construct(UserPopupQueueDecorator $queuePopup)
    {
        $this->queuePopup = $queuePopup;
    }

    /**
     * @property WhitelabelRaffleTicketLine $line
     *
     * @return void
     * */
    public function dispatchPrize(WhitelabelRaffleTicketLine $line): void
    {
        if ($isNotPrizeInKind = $line->prizeType()->notEquals(PrizeType::IN_KIND())) {
            return;
        }

        $ticket = $line->ticket;
        /** @var RaffleRuleTierInKindPrize $prize */
        $prize = $line->raffle_prize->tier->tier_prize_in_kind;

        $uniqueQueueId = serialize([$ticket->whitelabel_user_id, $prize->id]);
        $this->queuePopup->once($uniqueQueueId)->pushMessage(
            $ticket->whitelabel_id,
            $ticket->whitelabel_user_id,
            $prize->name,
            sprintf('You have won %s!', $prize->name)
        );
    }
}
