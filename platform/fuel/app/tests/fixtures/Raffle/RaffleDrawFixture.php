<?php

namespace Tests\Fixtures\Raffle;

use Carbon\Carbon;
use Models\Raffle;
use Models\RaffleDraw;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\CurrencyFixture;
use Tests\Fixtures\Raffle\RaffleFixture;

final class RaffleDrawFixture extends AbstractFixture
{
    public const RULE = 'raffle_rule';
    public const RAFFLE = 'raffle';
    public const CURRENCY = 'currency';

    public static function getClass(): string
    {
        return RaffleDraw::class;
    }

    public function getRandomNumbers(int $numbersCount): string
    {
        $randomNumbers = [[]];
        for ($i = 0; $i <= $numbersCount; $i++) {
            $randomNumbers[0][] = $this->faker->numberBetween(1, 40);
        }

        return json_encode($randomNumbers);
    }

    public function getDefaults(): array
    {
        return [
            'draw_no' => $this->faker->numberBetween(0, 50),
            'is_synchronized' => 1,
            'date' => Carbon::now()->addHour(),
            'draw_no' => $this->faker->randomNumber(4),
            'numbers' => $this->getRandomNumbers($this->faker->numberBetween(1, 20)),
            'is_calculated' => 1,
            'sale_sum' => $this->faker->randomNumber(4),
            'prize_total' => $this->faker->randomNumber(4),
            'lines_won_count' => $this->faker->numberBetween(1, 1000),
            'tickets_count' => $this->faker->numberBetween(1, 30),
        ];
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::RULE => $this->reference(self::RULE, RaffleRuleFixture::class),
            self::RAFFLE => $this->reference(self::RAFFLE, RaffleFixture::class),
            self::CURRENCY => $this->reference(self::CURRENCY, CurrencyFixture::class),
        ];
    }

    public function basic(): callable
    {
        return function (RaffleDraw $raffleDraw, array $attributes = []): void {
            if (empty($raffleDraw->currency)) {
                $raffleDraw->currency = $this->fixture(self::CURRENCY)->with('eur')->makeOne();
            }

            if (empty($raffleDraw->raffle)) {
                $raffle = $this->fixture(self::RAFFLE);
                $raffle->currency = $raffleDraw->currency;

                $raffleDraw->raffle = $raffle->makeOne();
            }

            if (empty($raffleDraw->rule)) {
                $rule = $this->fixture(self::RULE);
                $rule->currency = $raffleDraw->currency;
                $raffleDraw->rule = $rule->makeOne();
            }
        };
    }

    public function forRaffle(Raffle $raffle): self
    {
        $this->with(
            function (RaffleDraw $raffleDraw, array $attributes = []) use ($raffle): void {
                if (empty($raffleDraw->currency)) {
                    $raffleDraw->currency = $this->fixture(self::CURRENCY)->with('eur')->makeOne();
                }

                $raffleDraw->raffle = $raffle;
                $raffleDraw->rule = $raffle->rules[array_key_first($raffle->rules)];
                $raffleDraw->currency = $raffle->currency;
            }
        );
        return $this;
    }
}
