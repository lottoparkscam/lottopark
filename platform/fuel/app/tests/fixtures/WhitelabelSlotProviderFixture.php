<?php

namespace Tests\Fixtures;

use Models\WhitelabelSlotProvider;

final class WhitelabelSlotProviderFixture extends AbstractFixture
{
    public const WHITELABEL = 'whitelabel';
    public const SLOT_PROVIDER = 'slot_provider';

    public function getDefaults(): array
    {
        return [
            'is_enabled' => 1,
            'is_limit_enabled' => 0,
            'max_monthly_money_around_usd' => 50000.00
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelSlotProvider::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => function (WhitelabelSlotProvider $model, array $attributes = []) {
            },
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::SLOT_PROVIDER => $this->reference('slot_provider', SlotProviderFixture::class),
        ];
    }
}
