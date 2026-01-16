<?php

use Fuel\Core\Input;
use Models\WhitelabelBonus;

/**
 * @property array $edit
 * @property array $lotteries
 * @property array $raffles
 */
class Presenter_Whitelabel_Bonuses_Welcome extends Presenter_Presenter
{

    /**
     *
     * @return void
     */
    public function view(): void
    {
        $lotteryPurchaseClassError = isset($this->errors['input.lottery_purchase']) ? ' has-error' : '';
        $lotteryRegisterClassError = isset($this->errors['input.lottery_register']) ? ' has-error' : '';

        $this->set('lotteryPurchaseClassError', $lotteryPurchaseClassError);
        $this->set('lotteryRegisterClassError', $lotteryRegisterClassError);

        $this->set('registerWebsiteChecked', $this->edit['register_website'] ?? true);
        $this->set('registerApiChecked', $this->edit['register_api'] ?? true);

        /**
         * At the moment, it is not possible to combine all types of bonuses and lotteries.
         * We split the list of lotteries and raffles because lotteries are only supported for 'purchase' type
         * and raffle only for 'register' type.
         */
        $purchaseLotteries = $this->prepareLotteries(WhitelabelBonus::WELCOME_PURCHASE, WhitelabelBonus::WELCOME_TYPE_LOTTERY);
        $registerLotteries = $this->prepareLotteries(WhitelabelBonus::WELCOME_REGISTER, WhitelabelBonus::WELCOME_TYPE_RAFFLE);

        $this->set('purchaseLotteries', $purchaseLotteries);
        $this->set('registerLotteries', $registerLotteries);
    }

    protected function prepareLotteries(string $bonusType, string $lotteryType): array
    {
        $selected = ' selected="selected"';

        $preparedLotteries[0] = [
            'id' => 0,
            'name' => _('None'),
            'selected' => $selected,
        ];

        if ($lotteryType === WhitelabelBonus::WELCOME_TYPE_LOTTERY) {
            $this->addOptions($bonusType, $lotteryType, $this->lotteries, $preparedLotteries);
        }

        if ($lotteryType === WhitelabelBonus::WELCOME_TYPE_RAFFLE) {
            $this->addOptions($bonusType, $lotteryType, $this->raffles, $preparedLotteries);
        }

        return $preparedLotteries;
    }

    private function addOptions(string $bonusType, string $lotteryType, array $items, array &$preparedLotteries): void
    {
        $selected = ' selected="selected"';

        $idSelect = $this->getFormValue($bonusType);

        foreach ($items as $item) {

            $selectedValue = null;

            $id = $lotteryType. '_' . $item['id'];

            if ($idSelect === $id) {
                $preparedLotteries[0]['selected'] = null;
                $selectedValue = $selected;
            }

            $preparedLotteries[] = [
                'id' => $id,
                'name' => $item['name'],
                'selected' => $selectedValue,
            ];
        }
    }

    private function getFormValue(string $bonusType): ?string
    {
        if(Input::post('input.lottery_' . $bonusType) !== null) {
            return Input::post('input.lottery_' . $bonusType);
        }

        if(isset($this->edit[$bonusType]) && is_array($this->edit[$bonusType])){
            $lotteryType = key($this->edit[$bonusType]);

            return $lotteryType . '_' . $this->edit[$bonusType][$lotteryType];
        }

        return null;
    }
}
