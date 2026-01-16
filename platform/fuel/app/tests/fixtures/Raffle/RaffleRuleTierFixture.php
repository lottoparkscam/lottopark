<?php

namespace Tests\Fixtures\Raffle;

use Models\RaffleRule;
use Models\RaffleRuleTier;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\CurrencyFixture;
use fixtures\Exceptions\MissingRelation;

final class RaffleRuleTierFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const RULE = 'rule';
    public const RAFFLE = 'raffle';
    public const PRIZE_IN_KIND = 'raffle_prize_in_kind';

    public function getDefaults(): array
    {
        $matches = [[$this->faker->numberBetween(1, 10), $this->faker->numberBetween(11, 100)]];

        return [
            'slug' => substr($this->faker->slug(), 0, 45),
            'matches' => json_encode($matches),
            'prize_type' => 0, // todo
            'prize_fund_percent' => $this->faker->numberBetween(1, 100),
            'odds' => $this->faker->numberBetween(1, 1000), // todo
            'prize' => $this->faker->numberBetween(1, 10000),
            'is_main_prize' => $this->faker->boolean(20)
        ];
    }

    public static function getClass(): string
    {
        return RaffleRuleTier::class;
    }

    /** @inerhitDoc */
    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::CURRENCY => $this->reference(self::CURRENCY, CurrencyFixture::class),
            self::RULE => $this->reference(self::RULE, RaffleRuleFixture::class),
            self::PRIZE_IN_KIND => $this->reference(self::PRIZE_IN_KIND, RaffleRuleTierInKindPrizesFixture::class),
        ];
    }

    private function basic(): callable
    {
        return function (RaffleRuleTier $tier, array $attributes = []) {

            if (empty($tier->rule)) {
                $tier->rule = $this->fixture(self::RULE)->with($this::BASIC)->makeOne();
            }

            if (empty($tier->currency)) {
                $tier->currency = $this->fixture(self::CURRENCY)->makeOne();
            }

            if (empty($tier->tier_prize_in_kind)) {
                $tier->tier_prize_in_kind = $this->fixture(self::PRIZE_IN_KIND)->makeOne();
            }
        };
    }

    public function forRule(RaffleRule $rule): self
    {
        MissingRelation::verify($rule, 'currency');

        $this->with(
            function (RaffleRuleTier $tier, array $attributes = []) use ($rule) {
                $tier->rule = $rule;
                $tier->currency = $rule->currency;
            },
            $this->basic(),
        );
        return $this;
    }
}
