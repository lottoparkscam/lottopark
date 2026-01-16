<?php

namespace Tests\Fixtures\Raffle;

use Models\Raffle;
use Models\RafflePrize;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\Raffle\RaffleRuleTierFixture;

final class RafflePrizeFixture extends AbstractFixture
{
    public const DRAW = 'raffle_draw';
    public const TIER = 'raffle_rule_tier';

    public static function getClass(): string
    {
        return RafflePrize::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::DRAW => $this->reference(self::DRAW, RaffleDrawFixture::class),
            self::TIER => $this->reference(self::TIER, RaffleRuleTierFixture::class),
        ];
    }

    public function getDefaults(): array
    {
        return [
            'lines_won_count' => $this->faker->numberBetween(1, 10),
            'total' => $this->faker->numberBetween(0, 10),
            'per_user' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function basic(): callable
    {
        return function (RafflePrize $rafflePrize, array $attributes = []): void {
            if (empty($rafflePrize->draw)) {
                $rafflePrize->draw = $this->fixture(self::DRAW)->with(self::BASIC)->makeOne();
            }

            if (empty($rafflePrize->rule)) {
                $rafflePrize->rule = $rafflePrize->draw->rule;
                $rafflePrize->rule->currency = $rafflePrize->draw->currency;
            }

            if (empty($rafflePrize->tier)) {
                /** @var RaffleRuleTierFixture $tierFixture */
                $tierFixture = $this->fixture(self::TIER);
                $rafflePrize->tier = $tierFixture->forRule($rafflePrize->rule)->with('basic')->makeOne();
            }
        };
    }

    public function forRaffle(Raffle $raffle): self
    {
        $this->with(
            function (RafflePrize $rafflePrize, array $arguments = []) use ($raffle) {
                if (empty($rafflePrize->draw)) {
                    /** @var RaffleDrawFixture $raffleDrawFixture */
                    $raffleDrawFixture = $this->fixture(self::DRAW);
                    $raffleDrawFixture->forRaffle($raffle);
                    $rafflePrize->draw = $raffleDrawFixture->makeOne();
                }

                if (empty($rafflePrize->currency)) {
                    $rafflePrize->currency = $raffle->currency;
                }

                if (empty($rafflePrize->rule)) {
                    $rafflePrize->rule = $rafflePrize->draw->rule;
                }

                if (empty($rafflePrize->rule->currency)) {
                    $rafflePrize->rule->currency = $rafflePrize->draw->currency;
                }

                if (empty($rafflePrize->tier)) {
                    /** @var RaffleRuleTierFixture $tierFixture */
                    $tierFixture = $this->fixture(self::TIER);
                    $rafflePrize->tier = $tierFixture->forRule($rafflePrize->rule)->with(self::BASIC)->makeOne();
                }
            }
        );

        return $this;
    }
}
