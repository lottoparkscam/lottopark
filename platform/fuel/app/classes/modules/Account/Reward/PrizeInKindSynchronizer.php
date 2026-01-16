<?php

namespace Modules\Account\Reward;

use Helpers\RaffleHelper;
use Models\Lottery;
use Models\RafflePrize;
use Services_Raffle_Logger;
use Wrappers\Decorators\ConfigContract;

/**
 * If in moment if reward dispatching ticket prize, given lottery ticket price
 * has changed, then we have to update tiers & prizes.
 *
 * This method will also ensure data consistency when any of values will
 * change in DB or anywhere else.
 *
 * @url https://trello.com/c/UH0bECSX/1104-faireum-nowe-raffle
 */
class PrizeInKindSynchronizer
{
    private Lottery $lotteryDao;
    private Services_Raffle_Logger $logger;
    private ConfigContract $config;

    public function __construct(Lottery $lottery, Services_Raffle_Logger $logger, ConfigContract $config)
    {
        $this->lotteryDao = $lottery;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function recalculatePrizes(RafflePrize $prize): void
    {
        if ($this->isSyncDisabled()) {
            return;
        }

        $tierPrizeInKind = $prize->tier->tier_prize_in_kind;
        $lottery = $this->lotteryDao->get_by_slug(RaffleHelper::prizeInKindSlugToLotterySlug($tierPrizeInKind->slug));

        $tierWinners = $prize->tier->winners_count;
        $linesCount = $tierPrizeInKind->config['count'];

        $tierPrize = $lottery->price * $linesCount;
        $prizesAreTheSame = $tierPrize === $prize->per_user && $tierPrize === $tierPrizeInKind->per_user;

        if ($prizesAreTheSame) {
            return;
        }

        $newPrizeFromLotteryPrice = $lottery->price;

        $tierPrizeInKind->per_user = $tierPrizeInKind->config['count'] * $newPrizeFromLotteryPrice;
        $this->logger->log_info(
            $prize->raffle_rule_id,
            sprintf(
                'RaffleRuleTierInKindPrize #%d per_user changed to new lottery price %s.',
                $tierPrizeInKind->id,
                $newPrizeFromLotteryPrice
            )
        );

        $prize->per_user = $tierPrizeInKind->per_user;
        $prize->total = $tierPrizeInKind->per_user * $tierWinners;
        $this->logger->log_info(
            $prize->raffle_rule_id,
            sprintf('RafflePrize #%d per_user changed to new lottery price %s.', $prize->id, $newPrizeFromLotteryPrice)
        );

        $prize->tier->prize = $tierPrizeInKind->per_user;
        $this->logger->log_info(
            $prize->raffle_rule_id,
            sprintf('RaffleRuleTier #%d per_user changed to new lottery price %s.', $prize->tier->id, $newPrizeFromLotteryPrice)
        );
    }

    private function isSyncDisabled(): bool
    {
        return !$this->config->get('sync.raffle.update_prizes');
    }
}
