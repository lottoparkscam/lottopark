<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\{
    WhitelabelCampaign,
    WhitelabelPromoCode
};

final class WhitelabelPromoCodeFixture extends AbstractFixture
{
    public const WHITELABEL_CAMPAIGN = 'whitelabel_campaign';

    public function getDefaults(): array
    {
        return [
            'token' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPromoCode::class;
    }

    public function getStates(): array
    {
        return [
            self::WHITELABEL_CAMPAIGN => $this->reference('whitelabel_campaign', WhitelabelCampaignFixture::class),
        ];
    }

    public function withWhitelabelCampaign(WhitelabelCampaign $campaign): self
    {
        $this->with(function (WhitelabelPromoCode $whitelabelPromoCode) use ($campaign) {
            $whitelabelPromoCode->whitelabel_campaign_id = $campaign->id;
        });

        return $this;
    }
}
