<?php

namespace Tests\Fixtures\Raffle;

use Models\RaffleRule;
use Models\Raffle as Raffle;
use Models\WhitelabelRaffle;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\CurrencyFixture;
use fixtures\Exceptions\MissingRelation;

final class RaffleFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const RULE = 'rule';
    public const GGWORLD = 'ggworld';
    public const TEMPORARY_DISABLED = 'temporary_disabled';
    public const TEMPORARY_ENABLED = 'temporary_enabled';
    public const PLAYABLE = 'playable';
    public const WHITELABEL_RAFFLE = 'whitelabel_raffle';
    public const REGULAR_PRICE = 'regular_price';
    public const WL_BONUS_BALANCE_DISABLED = 'bonus_disabled';
    public const WL_BONUS_BALANCE_ENABLED = 'bonus_enabled';
    public const BETS50 = 'bets50';

    public function getDefaults(): array
    {
        return [
            'name' => $this->faker->company(),
            'country' => $this->faker->countryCode(),
            'country_iso' => $this->faker->countryCode(),
            'slug' => $this->faker->slug(2),
            'is_enabled' => $this->faker->boolean(),
            'is_sell_limitation_enabled' => $is_sell_limitation_enabled = $this->faker->boolean(),
            'is_sell_enabled' => $this->faker->boolean(),
            'timezone' => $this->faker->timezone(),
            'main_prize' => $this->faker->boolean(),
            'last_draw_date' => null,
            'last_draw_date_utc' => null,
            'next_draw_date' => null,
            'next_draw_date_utc' => null,
            'last_prize_total' => 0,
            'draw_lines_count' => 0,
            'last_ticket_count' => 0,
            'sell_open_dates' => null
        ];

        // todo: fix sell open dates array casting on save
        // !$is_sell_limitation_enabled ? [] : ["Mon 23:59", "Tue 23:59", "Wed 23:59", "Thu 23:59", "Fri 23:59", "Sat 23:59", "Sun 23:59"]
    }

    /** @inerhitDoc */
    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::RULE => $this->reference('rule', RaffleRuleFixture::class),
            self::GGWORLD => $this->ggWorld(),
            self::TEMPORARY_DISABLED => $this->temporaryDisabled(),
            self::TEMPORARY_ENABLED => $this->temporaryEnabled(),
            self::PLAYABLE => $this->playable(),
            self::WHITELABEL_RAFFLE => $this->reference(
                'whitelabel_raffle',
                WhitelabelRaffleFixture::class
            ),
            self::REGULAR_PRICE => $this->regularPrice(),
            self::WL_BONUS_BALANCE_DISABLED => fn(Raffle $r) => $r->whitelabel_raffle->is_bonus_balance_in_use = false,
            self::WL_BONUS_BALANCE_ENABLED => fn(Raffle $r) => $r->whitelabel_raffle->is_bonus_balance_in_use = true,
            self::BETS50 => $this->bets50(),
        ];
    }

    public static function getClass(): string
    {
        return Raffle::class;
    }

    private function ggWorld(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {

            $raffle->name = 'GG World Raffle';
            $raffle->slug = 'gg-world-raffle';
            $raffle->currency = $this->fixture(self::CURRENCY)->makeOne(['code' => 'USD']);
            /** @var RaffleRuleFixture $ruleFixture */
            $ruleFixture = $this->fixture(self::RULE);
            $raffle->rules = [$ruleFixture->stateFrom($raffle)->makeOne()];
        };
    }

    public function withDisabled(): self
    {
        $this->with($this->disabled());
        return $this;
    }

    public function withGGWorld(): self
    {
        $this->with($this->ggWorld());
        return $this;
    }

    private function basic(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {

            if (empty($raffle->currency)) {
                $raffle->currency = $this->fixture(self::CURRENCY)->makeOne(['code' => 'USD']);
            }

            if (empty($raffle->rules)) {
                $raffle->rules = $this->getRules($raffle, $attributes, rand(1, 5));
            }

            if (empty($raffle->whitelabel_raffle)) {
                $raffle->whitelabel_raffle = $this->getWlRaffle($raffle, $attributes);
            }
        };
    }

    private function disabled(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            $this->temporaryDisabled()($raffle, $attributes);
            $raffle->is_enabled = false;
        };
    }

    private function playable(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            $this->temporaryEnabled()($raffle, $attributes);
            $raffle->is_enabled = true;
        };
    }

    private function temporaryEnabled(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            $raffle->is_sell_limitation_enabled = true;
            $raffle->is_sell_enabled = true;
        };
    }

    private function temporaryDisabled(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            $raffle->is_sell_temporary_disabled = true;
            $raffle->is_sell_limitation_enabled = true;
            $raffle->is_sell_enabled = false;
            $raffle->sell_open_dates = array_map(
                fn() => $this->faker->date('Y-m-d H:i:s'),
                range(1, rand(1, 10))
            );
        };
    }

    /**
     * Returns constant line price (easy to calculate).
     *
     * @return callable
     */
    private function regularPrice(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            if (!empty($raffle->rules)) {
                $raffle->getFirstRule()->line_price = 10;
                $raffle->getFirstRule()->fee = 1;
                return;
            }

            $raffle->rules = [
                $this->fixture(self::RULE)->makeOne(
                    [
                        'line_price' => 10,
                        'fee' => 1,
                    ]
                )
            ];
        };
    }

    private function bets50(): callable
    {
        return function (Raffle $raffle, array $attributes = []) {
            MissingRelation::verify($raffle, 'rules', 'whitelabel_raffle');
            MissingRelation::verify($raffle->whitelabel_raffle, 'provider');

            $raffle->getFirstRule()->max_lines_per_draw = 50;
            $raffle->whitelabel_raffle->provider->max_bets = 50;
        };
    }

    /**
     * @param Raffle $raffle
     * @param array $attributes
     * @param $count
     * @return array<RaffleRule>
     */
    private function getRules(Raffle $raffle, array $attributes, $count): array
    {
        /** @var RaffleRuleFixture $ruleFixture */
        $ruleFixture = $this->fixture(self::RULE);
        $ruleFixture->stateFrom($raffle)->makeMany([], $count);
        return $ruleFixture->makeMany($attributes, $count);
    }

    private function getWlRaffle(Raffle $raffle, array $attributes): WhitelabelRaffle
    {
        /** @var WhitelabelRaffleFixture $wlRaffleFixture */
        $wlRaffleFixture = $this->fixture(self::WHITELABEL_RAFFLE);
        return $wlRaffleFixture->withRaffle($raffle)->with('basic')($attributes);
    }
}
