<?php

namespace Tests\Fixtures\Raffle;

use Helpers\StringHelper;
use Models\Raffle;
use Models\RaffleRuleTierInKindPrize;
use Tests\Fixtures\AbstractFixture;

final class RaffleRuleTierInKindPrizesFixture extends AbstractFixture
{
    public static function getClass(): string
    {
        return RaffleRuleTierInKindPrize::class;
    }

    public function getDefaults(): array
    {
        return [
            'name' => $productName = $this->faker->name(),
            'slug' => StringHelper::slugify($productName),
            'type' => 'ticket',
            'per_user' => $this->faker->numberBetween(1, 400),
            'config' => json_encode(['count' => $this->faker->numberBetween(1, 100)]),
        ];
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
        ];
    }

    public function basic(): callable
    {
        return function (RaffleRuleTierInKindPrize $raffleRuleTierInKindPrizes, array $attributes = []): void {
        };
    }

    public function forRaffle(Raffle $raffle): callable
    {
        return function (RaffleRuleTierInKindPrize $raffleRuleTierInKindPrizes, array $attributes = []) use ($raffle) {
            $raffleRuleTierInKindPrizes->raffle = $raffle;
        };
    }
}
