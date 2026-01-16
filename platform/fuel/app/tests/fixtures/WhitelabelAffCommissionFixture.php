<?php

namespace Tests\Fixtures;

use Models\WhitelabelAffCommission;
use Tests\Fixtures\AbstractFixture;

class WhitelabelAffCommissionFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'type' => $this->faker->boolean,
            'tier' => $this->faker->boolean,
            'commission' => $this->faker->randomFloat(2),
            'commission_usd' => $this->faker->randomFloat(2),
            'commission_payment' => $this->faker->randomFloat(2),
            'commission_manager' => $this->faker->randomFloat(2),
            'is_accepted' => $this->faker->boolean,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelAffCommission::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (WhitelabelAffCommission $model, array $attributes = []) {
            },
        ];
    }
}
