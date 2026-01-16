<?php

namespace Tests\Fixtures;

use Models\SlotProvider;

final class SlotProviderFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'slug' => $this->faker->slug(),
            'api_url' => $this->faker->url(),
            'init_game_path' => '/games/init',
            'init_demo_game_path' => '/games/init-demo',
            'api_credentials' => '{}',
            'game_list_path' => '/games'
        ];
    }

    public static function getClass(): string
    {
        return SlotProvider::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (SlotProvider $model, array $attributes = []) {
            },
        ];
    }
}
