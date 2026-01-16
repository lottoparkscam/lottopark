<?php

namespace Tests\Fixtures;

use Models\WhitelabelPaymentMethod;
use Models\WhitelabelPaymentMethodCustomize;

final class WhitelabelPaymentMethodCustomizeFixture extends AbstractFixture
{
    public const WHITELABEL_PAYMENT_METHOD = 'whitelabel_payment_method';

    /** @inheritdoc */
    public function getDefaults(): array
    {
        return [
            'title' => '',
            'title_for_mobile' => '',
            'title_in_description' => '',
            'description' => null,
            'additional_failure_text' => null,
            'additional_success_text' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPaymentMethodCustomize::class;
    }

    /** @inheritdoc */
    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
        ];
    }

    private function basic(): callable
    {
        return function (WhitelabelPaymentMethodCustomize $wlPaymentMethodCustomize, array $attributes = []): void {

            if (empty($wlPaymentMethodCustomize->whitelabel_payment_method)) {

                /** @var WhitelabelPaymentMethod $wlPaymentMethod */
                $wlPaymentMethod = $this->fixture(self::WHITELABEL_PAYMENT_METHOD)->with('basic')->makeOne();

                $wlPaymentMethodCustomize->whitelabel_payment_method = $wlPaymentMethod;
                $wlPaymentMethodCustomize->whitelabel_payment_method->whitelabel_language_id = $wlPaymentMethod->language_id;
            }
        };
    }

    public function withWhitelabelPaymentMethod(WhitelabelPaymentMethod $wlPaymentMethod): self
    {
        $this->with(function (WhitelabelPaymentMethodCustomize $wlPaymentMethodCustomize, array $attributes = []) use ($wlPaymentMethod) {
            $wlPaymentMethodCustomize->whitelabel_payment_method = $wlPaymentMethod;
        });

        return $this;
    }

    public function withWhitelabelLanguageId(int $languageId): self
    {
        $this->with(function (WhitelabelPaymentMethodCustomize $wlPaymentMethodCustomize, array $attributes = []) use ($languageId) {
            $wlPaymentMethodCustomize->whitelabel_language_id = $languageId;
        });

        return $this;
    }
}
