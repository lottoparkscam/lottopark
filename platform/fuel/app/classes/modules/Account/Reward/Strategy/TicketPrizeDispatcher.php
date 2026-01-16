<?php

namespace Modules\Account\Reward\Strategy;

use Helpers\RaffleHelper;
use Models\Lottery;
use Models\RaffleRuleTierInKindPrize;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeInKindSynchronizer;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\RewardDispatchingStrategyContract;
use Modules\Account\UserPopupQueueDecorator;
use RuntimeException;
use Services\Lottery\Factory\TicketFactory;
use Webmozart\Assert\Assert;

class TicketPrizeDispatcher implements RewardDispatchingStrategyContract
{
    private TicketFactory $factory;
    private PrizeInKindSynchronizer $prizeSynchronizer;
    private Lottery $lotteryDao;
    private UserPopupQueueDecorator $queue_popup;

    public function __construct(
        TicketFactory $factory,
        Lottery $lottery,
        PrizeInKindSynchronizer $prizeSynchronizer,
        UserPopupQueueDecorator $queuePopup
    ) {
        $this->factory = $factory;
        $this->prizeSynchronizer = $prizeSynchronizer;
        $this->lotteryDao = $lottery;
        $this->queue_popup = $queuePopup;
    }

    public function dispatchPrize(WhitelabelRaffleTicketLine $line): void
    {
        if ($isNotTicketType = $line->prizeType()->notEquals(PrizeType::TICKET())) {
            return;
        }

        $prize = $line->raffle_prize->tier->tier_prize_in_kind;

        $this->verifyPrizeLotterySchema($prize);
        $this->verifyLotteryIsPlayable(RaffleHelper::prizeInKindSlugToLotterySlug($prize->slug));

        $this->createBonusTicket($line);

        $this->queuePopupMessage($line);
    }

    private function verifyPrizeLotterySchema(RaffleRuleTierInKindPrize $prize): void
    {
        Assert::keyExists($prize->config, 'count', sprintf('Unable to find config->count key in %s.', get_class($prize)));
    }

    private function verifyLotteryIsPlayable(string $lotterySlug): void
    {
        $lottery = $this->lotteryDao->get_by_slug($lotterySlug);

        if ($isNotPlayable = false === $lottery->isEnabled) {
            throw new RuntimeException(sprintf('Unable to dispatch ticket prize due lottery %s is disabled.', $lottery->slug));
        }
    }

    private function createBonusTicket(WhitelabelRaffleTicketLine $line): void
    {
        $ticket = $line->ticket;
        $prize = $line->raffle_prize->tier->tier_prize_in_kind;
        $linesCount = $prize->config['count'];

        $this->factory->create_bonus_ticket(
            $ticket->whitelabel_id,
            $ticket->whitelabel_user_id,
            RaffleHelper::prizeInKindSlugToLotterySlug($prize->slug),
            $linesCount
        );
    }

    private function queuePopupMessage(WhitelabelRaffleTicketLine $line): void
    {
        $ticket = $line->ticket;
        /** @var RaffleRuleTierInKindPrize $prize */
        $prize = $line->raffle_prize->tier->tier_prize_in_kind;

        $uniqueQueueId = serialize([$ticket->whitelabel_user_id, $line->raffle_prize->raffle_rule_tier_id]);
        $this->queue_popup->once($uniqueQueueId)->pushMessage(
            $ticket->whitelabel_id,
            $ticket->whitelabel_user_id,
            $prize->name,
            'You have won free ticket!'
        );
    }
}
