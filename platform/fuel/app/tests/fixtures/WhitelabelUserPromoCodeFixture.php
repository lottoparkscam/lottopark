<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\{
    WhitelabelPromoCode,
    WhitelabelTransaction,
    WhitelabelUserPromoCode,
    WhitelabelUser
};

final class WhitelabelUserPromoCodeFixture extends AbstractFixture
{
    public const WHITELABEL_PROMO_CODE = 'whitelabel_promo_code';
    public const WHITELABEL_TRANSACTION = 'whitelabel_transaction';
    public const WHITELABEL_USER = 'whitelabel_user';

    public function getDefaults(): array
    {
        return [];
    }

    public static function getClass(): string
    {
        return WhitelabelUserPromoCode::class;
    }

    public function getStates(): array
    {
        return [
            self::WHITELABEL_PROMO_CODE => $this->reference('whitelabel_promo_code', WhitelabelPromoCodeFixture::class),
            self::WHITELABEL_TRANSACTION => $this->reference('whitelabel_transaction', WhitelabelTransactionFixture::class),
            self::WHITELABEL_USER => $this->reference('whitelabel_user', WhitelabelUserFixture::class),
        ];
    }

    public function withWhitelabelPromoCode(WhitelabelPromoCode $whitelabelPromoCode): self
    {
        $this->with(function (WhitelabelUserPromoCode $whitelabelUserPromoCode) use ($whitelabelPromoCode) {
            $whitelabelUserPromoCode->whitelabel_promo_code_id = $whitelabelPromoCode->id;
        });

        return $this;
    }

    public function withWhitelabelTransaction(WhitelabelTransaction $whitelabelTransaction): self
    {
        $this->with(function (WhitelabelUserPromoCode $whitelabelUserPromoCode) use ($whitelabelTransaction) {
            $whitelabelUserPromoCode->whitelabel_transaction_id = $whitelabelTransaction->id;
        });

        return $this;
    }

    public function withWhitelabelUser(WhitelabelUser $whitelabelUser): self
    {
        $this->with(function (WhitelabelUserPromoCode $whitelabelUserPromoCode) use ($whitelabelUser) {
            $whitelabelUserPromoCode->whitelabel_user_id = $whitelabelUser->id;
        });

        return $this;
    }
}
