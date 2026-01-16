<?php

declare(strict_types=1);

namespace Services;

use Models\{
    Whitelabel,
    WhitelabelAff,
    WhitelabelCampaign,
    WhitelabelUserAff,
    WhitelabelUserPromoCode
};
use Repositories\Aff\WhitelabelAffRepository;
use Container;
use Helpers_Aff_Refreader;
use Lotto_Helper;
use InvalidArgumentException;

class AffUserService
{
    private ?Whitelabel $whitelabel;
    private WhitelabelAffRepository $whitelabelAffRepository;
    private Helpers_Aff_Refreader $refReader;

    private ?WhitelabelUserPromoCode $userPromoCode = null;
    private ?WhitelabelCampaign $campaign = null;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->refReader = Container::get(Helpers_Aff_Refreader::class);
    }

    public function getRefReader(): Helpers_Aff_Refreader
    {
        return $this->refReader;
    }

    public function addCampaign(?array $campaign): void
    {
        if (isset($campaign['whitelabel_aff_id'])) {
            $this->campaign = new WhitelabelCampaign($campaign);
        }
    }

    public function isCampaignApplicable(): bool
    {
        return $this->campaign !== null;
    }

    public function createUser(int $userId): void
    {
        if (empty($this->whitelabel)) {
            throw new InvalidArgumentException('Unable to create user: Whitelabel is not set.');
        }

        $whitelabelAff = $this->findWhitelabelAff();

        if (!empty($whitelabelAff)) {
            $whitelabelUserAff = WhitelabelUserAff::forge();

            $whitelabelUserAff->set([
                'whitelabel_id' => $this->whitelabel->id,
                'whitelabel_user_id' => $userId,
                'whitelabel_aff_id' => $whitelabelAff->id,
                'is_deleted' => 0,
                'is_accepted' => $this->isAutoAccept(),
                'is_expired' => 0,
                'is_casino' => IS_CASINO
            ]);

            if (!$this->isCampaignApplicable() && $this->refReader->isRefValid()) {
                Lotto_Helper::check_user_aff($whitelabelAff, $whitelabelUserAff);
            }

            $whitelabelUserAff->save();
        }
    }

    private function findWhitelabelAff(): ?WhitelabelAff
    {
        if ($this->isCampaignApplicable()) {
            return $this->whitelabelAffRepository->findAffiliateById($this->campaign->whitelabel_aff_id);
        }

        if ($this->refReader->isRefValid()) {
            return $this->whitelabelAffRepository->findAffiliateByToken($this->whitelabel->id, $this->refReader->get_ref_token());
        }

        return null;
    }

    private function isAutoAccept(): bool
    {
        if ($this->isCampaignApplicable()) {
            return true;
        }

        return (bool) $this->whitelabel->affLeadAutoAccept;
    }
}