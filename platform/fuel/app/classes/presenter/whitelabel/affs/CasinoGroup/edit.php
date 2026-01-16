<?php

use Services\AffiliateGroupService;

/**
 * View for: platform/fuel/app/views/whitelabel/affs/CasinoGroup/edit.php
 */
class Presenter_Whitelabel_Affs_CasinoGroups_Edit extends Presenter_Presenter
{
    public function view(): void
    {
        $this->set('isCasinoAffGroup', true);
        $this->set('groupId', $this->id);
        $this->set('groupName', $this->getGroupName());
        $this->set('casinoGroup', $this->prepareCasinoGroup());
    }

    private function isDefaultGroup(): bool
    {
        return $this->id === AffiliateGroupService::DEFAULT_CASINO_AFF_GROUP_ID;
    }

    private function getGroupName(): string
    {
        return $this->isDefaultGroup() ? 'Default Group' : $this->casinoGroup['name'];
    }

    private function prepareCasinoGroup(): array
    {
        if ($this->isDefaultGroup()) {
            return $this->prepareDefaultCasinoGroup();
        }

        return $this->casinoGroup;
    }

    private function prepareDefaultCasinoGroup(): array
    {
        $defaultCasinoGroupData = [];

        $defaultCasinoGroupData['commission_percentage_value_for_tier_1'] =
            $this->whitelabel['default_casino_commission_percentage_value_for_tier_1'] ?: 0;

        $defaultCasinoGroupData['commission_percentage_value_for_tier_2'] =
            $this->whitelabel['default_casino_commission_percentage_value_for_tier_2'] ?: 0;

        return $defaultCasinoGroupData;
    }
}
