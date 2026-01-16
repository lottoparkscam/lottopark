<?php

namespace Modules\Mediacle\Models;

use Carbon\Carbon;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelPlugin;
use Models\WhitelabelCampaign;
use Modules\Mediacle\MediaclePlugin;

class PlayerDataWhitelabelUserModelAdapter implements MediaclePlayerRegistrationData
{
    private WhitelabelUser $user;

    public function __construct(WhitelabelUser $user)
    {
        $this->user = $user;
    }

    public function getFirstName(): ?string
    {
        return $this->user->name;
    }

    public function getLastName(): ?string
    {
        return $this->user->surname;
    }

    public function getEmail(): string
    {
        return $this->user->email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->user->phone ?? null;
    }

    public function getCompany(): ?string
    {
        return $this->user->company ?? null;
    }

    /**
     * Attention - I assumed, wl can have only one plugin of the same kind
     *             that why the code below might work.
     *
     * Returned value must provide an API key, required by MediacleRepository (check documentation for more info).
     *
     * @return string
     */
    public function getTrackingIdentityKey(): string
    {
        $plugins = $this->user->whitelabel->whitelabelPlugins;
        $mediacle = array_values(
            array_filter(
                $plugins,
                fn (WhitelabelPlugin $p) => $p->plugin === MediaclePlugin::NAME && $p->whitelabel_id == $this->user->whitelabel_id
            ) ?? []
        );
        /** @var WhitelabelPlugin $mediacle */
        $mediacle = $mediacle[0];
        return $mediacle->options['key'];
    }

    public function getPlayerId(): string
    {
        return $this->user->getPrefixedToken();
    }

    public function getBrand(): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->user->whitelabel;
        return $whitelabel->name;
    }

    public function getCountryCode(): ?string
    {
        return $this->user->last_country;
    }

    public function getAccountOpeningDate(): string
    {
        return $this->user->date_register;
    }

    public function getPromoCode(): ?string
    {
        /** @var WhitelabelCampaign $campaign */
        $campaign = $this->user->whitelabel_user_promo_code->whitelabel_promo_code->whitelabel_campaign ?? null;
        if ($campaign !== null && $campaign->isRegister()) {
            return $this->user->whitelabel_user_promo_code->whitelabel_promo_code->whitelabel_campaign->token;
        }
        return null;
    }

    public function getTrackingId(): ?string
    {
        return $this->user->whitelabel_user_aff->whitelabel_aff->token ?? null;
    }

    public function getTimeStamp(): int
    {
        return (new Carbon($this->user->date_register))->getTimestamp();
    }

    public function getBtag(): ?string
    {
        return $this->user->whitelabel_user_aff->btag ?? null;
    }
}
