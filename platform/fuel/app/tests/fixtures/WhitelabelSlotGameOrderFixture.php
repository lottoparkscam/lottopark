<?php

namespace Tests\Fixtures;

use Models\WhitelabelSlotGameOrder;

final class WhitelabelSlotGameOrderFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'order_json' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelSlotGameOrder::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (WhitelabelSlotGameOrder $model, array $attributes = []) {
            },
        ];
    }
}
