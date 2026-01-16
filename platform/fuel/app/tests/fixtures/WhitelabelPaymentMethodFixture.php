<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\Whitelabel;
use Models\WhitelabelPaymentMethod;

final class WhitelabelPaymentMethodFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const WHITELABEL = 'whitelabel';

    /** @inheritdoc */
    public function getDefaults(): array
    {
        return [
            'name' => $this->faker->name(),
            'show' => 1,
            'data' => serialize([]),
            'data_json' => null,
            'order' => 0,
            'cost_percent' => 0.00,
            'cost_fixed' => 0.00,
            'cost_currency_id' => null,
            'payment_currency_id' => 2,
            'show_payment_logotype' => 1,
            'custom_logotype' => null,
            'only_deposit' => 0,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPaymentMethod::class;
    }

    /** @inheritdoc */
    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
        ];
    }

    private function basic(): callable
    {
        return function (WhitelabelPaymentMethod $wlPaymentMethod, array $attributes = []): void {

            if (empty($wlPaymentMethod->whitelabel)) {
                $wlPaymentMethod->whitelabel = $this->fixture(self::WHITELABEL)->with('basic')->makeOne();
            }
        };
    }

    public function withWhitelabel(Whitelabel $wl): self
    {
        $this->with(function (WhitelabelPaymentMethod $wlPaymentMethod, array $attributes = []) use ($wl) {
            $wlPaymentMethod->whitelabel = $wl;
        });

        return $this;
    }
}
