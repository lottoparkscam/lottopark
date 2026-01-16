<?php

namespace Tests\Fixtures;

use Models\{
    Whitelabel,
    WhitelabelAff,
    WhitelabelCampaign
};
use Helpers_General;
use Carbon\Carbon;

final class WhitelabelCampaignFixture extends AbstractFixture
{
    public const WHITELABEL = 'whitelabel';
    public const WHITELABEL_AFF = 'whitelabel_aff';

    private string $prefix = 'TEST_CODE';

    /** @inheritdoc */
    public function getDefaults(): array
    {
        return [
            'token' => $this->faker->numberBetween(1000, 999999),
            'bonus_type' => Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE,
            'type' => Helpers_General::PROMO_CODE_TYPE_REGISTER,
            'lottery_id' => 1,
            'max_codes_user' => null,
            'max_users_per_code' => null,
            'prefix' => $this->prefix,
            'is_active' => 1,
            'date_start' => $this->faker->date(),
            'date_end' => $this->faker->date('Y-m-d H:i:s', '+1 month'),
            'max_users' => null,
            'discount_amount' => null,
            'discount_type' => null,
            'bonus_balance_amount' => null,
            'bonus_balance_type' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelCampaign::class;
    }

    /** @inheritdoc */
    public function getStates(): array
    {
        return [
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::WHITELABEL_AFF => $this->reference('whitelabel_aff', WhitelabelAffFixture::class),
        ];
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function withWhitelabel(Whitelabel $wl): self
    {
        $this->with(function (WhitelabelCampaign $campaign) use ($wl) {
            $campaign->whitelabel_id = $wl->id;
        });

        return $this;
    }

    public function withWhitelabelAff(WhitelabelAff $aff): self
    {
        $this->with(function (WhitelabelCampaign $campaign) use ($aff) {
            $campaign->whitelabel_aff_id = $aff->id;
        });

        return $this;
    }

    public function withBonusTypeFreeLine(?int $lotteryId): self
    {
        $this->with(function (WhitelabelCampaign $campaign) use ($lotteryId) {
            $campaign->bonus_type = Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE;
            $campaign->lottery_id = $lotteryId;
        });

        return $this;
    }

    public function withBonusTypeDiscount(float $amount, int $type = Helpers_General::PROMO_CODE_DISCOUNT_TYPE_PERCENT): self
    {
        $this->with(function (WhitelabelCampaign $campaign) use ($amount, $type) {
            $campaign->bonus_type = Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT;
            $campaign->discount_amount = $amount;
            $campaign->discount_type = $type;
        });

        return $this;
    }

    public function withBonusTypeBalance(float $amount, int $type = Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT): self
    {
        $this->with(function (WhitelabelCampaign $campaign) use ($amount, $type) {
            $campaign->bonus_type = Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY;
            $campaign->bonus_balance_amount = $amount;
            $campaign->bonus_balance_type = $type;
        });

        return $this;
    }

    public function withValidityThisMonth(): self
    {
        $this->with(function (WhitelabelCampaign $campaign) {
            $campaign->date_start = Carbon::today();
            $campaign->date_end = Carbon::parse('+1 month');
        });

        return $this;
    }
}
