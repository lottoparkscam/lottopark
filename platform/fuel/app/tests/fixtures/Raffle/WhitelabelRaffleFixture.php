<?php

namespace Tests\Fixtures\Raffle;

use Models\Raffle;
use Models\RaffleProvider;
use Model_Lottery_Provider;
use Models\WhitelabelRaffle;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\WhitelabelFixture;

final class WhitelabelRaffleFixture extends AbstractFixture
{
    public const WHITELABEL = 'whitelabel';
    public const RAFFLE = 'raffle';

    // raffle context, mandatory to be used in this fixture
    private ?Raffle $raffle = null;

    public function getDefaults(): array
    {
        return [
            'income' => $this->faker->numberBetween(1, 100),
            'income_type' => 0,
            'is_enabled' => $this->faker->boolean(75),
            'is_margin_calculation_enabled' => $this->faker->boolean(75),
            'is_bonus_balance_in_use' => 0,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelRaffle::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::RAFFLE => $this->reference('raffle', WhitelabelFixture::class),
        ];
    }

    private function basic(): callable
    {
        return function (WhitelabelRaffle $wlRaffle, array $attributes = []): void {

            if (empty($wlRaffle->raffle)) {
                $wlRaffle->raffle = $this->fixture(self::RAFFLE)->with('basic')();
            }

            if (empty($wlRaffle->whitelabel)) {
                $wlRaffle->whitelabel = $this->fixture(self::WHITELABEL)->with('basic')->makeOne();
            }

            $hasRules = !empty($wlRaffle->raffle->rules);
            $maxBets = $hasRules ?
                $wlRaffle->raffle->getFirstRule()->max_lines_per_draw : rand(1, 10) * 100;

            // todo create Factory
            $provider = new RaffleProvider();
            $provider->min_bets = 1;
            $provider->max_bets = $maxBets;
            $provider->multiplier = 0;
            $provider->timezone = $this->faker->timezone();
            $provider->raffle = $wlRaffle->raffle ?? $this->fixture(self::RAFFLE)->with('basic')();
            $provider->offset = 0;
            $provider->tax = 0;
            $provider->tax_min = 0;
            $provider->provider = $this->faker->randomElement(
                [
                    Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER,
                    Model_Lottery_Provider::LOTTORISQ,
                    Model_Lottery_Provider::NONE,
                ]
            );
            $wlRaffle->provider = $provider;
        };
    }

    public function withRaffle(Raffle $raffle): self
    {
        $this->with(function (WhitelabelRaffle $wlRaffle, array $attributes = []) use ($raffle): void {
            $wlRaffle->raffle = $raffle;
        });
        return $this;
    }
}
