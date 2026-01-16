<?php

namespace Tests\Fixtures;

use Models\WhitelabelUserAff;

class WhitelabelUserAffFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'externalId' => '1',
            'btag' => $this->faker->uuid(),
            'is_deleted' => 0,
            'is_accepted' => 1,
            'is_expired' => 0,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelUserAff::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (WhitelabelUserAff $model, array $attributes = []) {
            },
        ];
    }
}
