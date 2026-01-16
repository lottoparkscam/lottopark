<?php

namespace Tests\Fixtures\Raffle;

use Models\Raffle;
use Models\RaffleRule;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\CurrencyFixture;
use fixtures\Exceptions\MissingRelation;

final class RaffleRuleFixture extends AbstractFixture
{
    public const GGWORLD = 'ggworld';
    public const CURRENCY = 'currency';
    public const TIERS = 'tiers';
    public const RAFFLE = 'raffle';

    public function getDefaults(): array
    {
        return [
            'line_price' => $linePrice = $this->faker->numberBetween(1, 10),
            'fee' => $this->faker->numberBetween(1, $linePrice / 2) / 10,
            'max_lines_per_draw' => $maxLines = rand(1, 10) * 10,
            'ranges' => json_encode([[1, $maxLines]]) // todo
        ];
    }

    public static function getClass(): string
    {
        return RaffleRule::class;
    }

    /** @inheritDoc */
    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference(self::CURRENCY, CurrencyFixture::class),
            self::GGWORLD => $this->ggWorld(),
            self::TIERS => $this->reference(self::TIERS, RaffleRuleTierFixture::class),
            self::BASIC => $this->basic(),
            self::RAFFLE => $this->reference('raffle', RaffleFixture::class),
        ];
    }

    private function ggWorld(): callable
    {
        return function (RaffleRule $rule, array $attributes = []) {
            $rule->currency = $this->fixture(self::CURRENCY)->makeOne($attributes);
            $rule->line_price = 10;
            $rule->fee = 0.5; // todo
            $rule->max_lines_per_draw = 1000;
            $rule->ranges = [[1, $rule->max_lines_per_draw]];
        };
    }

    public function stateFrom(Raffle $raffle): self
    {
        $this->with(
            function (RaffleRule $rule, array $attributes = []) use ($raffle) {
                MissingRelation::verify($raffle, 'currency');
                $rule->currency = $raffle->currency;

                $tiers = [];

                /** @var RaffleRuleTierFixture $tiersFixture */
                $tiersFixture = $this->fixture(self::TIERS);
                $tiersFixture->forRule($rule);

                switch ($raffle->slug) {
                    case 'gg-world-raffle':
                        $tiers[] = $tiersFixture->makeOne(['slug' => 'raffle-closed:1', 'matches' => json_encode([1])]);
                        $tiers[] = $tiersFixture->makeOne(
                            [
                                'slug' => 'raffle-closed:2_25',
                                'matches' => json_encode([[2, 25]])
                            ]
                        );
                        break;

                    default:
                        $tiers = $tiersFixture->makeMany([], $this->faker->numberBetween(2, 10));
                        break;
                }

                $rule->tiers = $tiers;
            }
        );
        return $this;
    }

    private function basic(): callable
    {
        return function (RaffleRule $rule, array $attributes = []) {
            if (empty($rule->raffle)) {
                $rule->raffle = $this->fixture(self::RAFFLE)->with(self::BASIC)->makeOne();
            }

            if (empty($rule->currency)) {
                MissingRelation::verify($rule->raffle, 'currency');
                $rule->currency = $rule->raffle->currency;
            }

            // todo: Add tier for Rule method and initialize with currency
            // see: https://trello.com/c/jg6eCOe2
        };
    }

    public function forRaffle(Raffle $raffle): self
    {
        $this->with(
            function (RaffleRule $raffleRule) use ($raffle): void {
                $raffleRule->raffle = $raffle;
                $raffleRule->currency = $raffle->currency;
            }
        );

        return $this;
    }
}
