<?php

namespace Tests\Fixtures;

use Models\SlotGame;

final class SlotGameFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'is_deleted' => 0,
            'name' => $this->faker->word(),
            'image' => $this->faker->gravatar(),
            'type' => $this->faker->randomElement(['slots']),
            'provider' => $this->faker->randomElement(['OneTouch', 'Yggdrasil']),
            'technology' => 'HTML5',
            'has_demo' => 1,
            'has_lobby' => 0,
            'has_freespins' => 0,
            'is_mobile' => 0,
            'order' => 1,
            'freespin_valid_until_full_day' => 0,
        ];
    }

    public static function getClass(): string
    {
        return SlotGame::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (SlotGame $model, array $attributes = []) {
            },
        ];
    }
}
