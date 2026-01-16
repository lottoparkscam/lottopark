<?php

/**
 * View for: platform/fuel/app/views/whitelabel/affs/CasinoGroup/index.php
 */
class Presenter_Whitelabel_Affs_CasinoGroups_Index extends Presenter_Presenter
{
    public function view(): void
    {
        $this->set('isCasinoAffGroup', true);
        $this->set('defaultCasinoGroups', $this->prepareDefaultCasinoGroup());
        $this->set('casinoGroups', $this->prepareCasinoGroups());
    }

    private function formatCommissionPercentageValue(float $commissionValue): string
    {
        $formattedPercentageValue = round(
            $commissionValue / 100,
            Helpers_Currency::RATE_SCALE
        );
        $formattedPercentageValue = Lotto_View::format_percentage($formattedPercentageValue);

        return $formattedPercentageValue;
    }

    private function prepareDefaultCasinoGroup(): array
    {
        $defaultCasinoGroupData = [];

        $defaultCommissionPercentageValueForTier1 =
            $this->whitelabel['default_casino_commission_percentage_value_for_tier_1'];

        $defaultCommissionPercentageValueForTier2 =
            $this->whitelabel['default_casino_commission_percentage_value_for_tier_2'];

        if (!empty($defaultCommissionPercentageValueForTier1)) {
            $defaultCasinoGroupData['commission_percentage_value_tier_1'] =
            $this->formatCommissionPercentageValue($defaultCommissionPercentageValueForTier1);
        }

        if (!empty($defaultCommissionPercentageValueForTier2)) {
            $defaultCasinoGroupData['commission_percentage_value_tier_2'] =
                $this->formatCommissionPercentageValue($defaultCommissionPercentageValueForTier2);
        }

        return $defaultCasinoGroupData;
    }

    private function prepareCasinoGroups(): array
    {
        $preparedCasinoGroups = [];
        foreach ($this->casinoGroups as $casinoGroup) {
            $preparedCasinoGroups[] = [
                'id' => $casinoGroup->id,
                'name' => $casinoGroup->name,
                'commissionPercentageValueForTier1' => $this->formatCommissionPercentageValue(
                    $casinoGroup->commissionPercentageValueForTier1
                ),
                'commissionPercentageValueForTier2' => $this->formatCommissionPercentageValue(
                    $casinoGroup->commissionPercentageValueForTier2
                ),
            ];
        }

        return $preparedCasinoGroups;
    }
}
