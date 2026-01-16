<?php

/**
 * Description of Presenter_Whitelabel_Lottery_Settings_List
 */
class Presenter_Whitelabel_Lottery_Settings_List extends Presenter_Presenter
{
    /**
     *
     * @var bool
     */
    private $show_asterisk = false;
    
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $prepared_lotteries = $this->prepare_lottery_data();
        $this->set("lotteries", $prepared_lotteries);
        $this->set("show_asterisk", $this->show_asterisk);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_lottery_data(): array
    {
        $prepared_lottery_data = [];
        
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );
        
        $providers = [
            Helpers_General::PROVIDER_IMVALAP => _("Imvalap"),
            Helpers_General::PROVIDER_LOTTORISQ => _("Lottorisq"),
            Helpers_General::PROVIDER_NONE => _("Insurance/None")
        ];

        $models = [
            Helpers_General::LOTTERY_MODEL_PURCHASE => _("Purchase"),
            Helpers_General::LOTTERY_MODEL_MIXED => _("Mixed (Insurance/Purchase)"),
            Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN => _("Purchase + Scan"),
            Helpers_General::LOTTERY_MODEL_NONE => _("None")
        ];

        $i = 0;
        foreach ($this->lotteries as $key => $lottery) {
            if (substr($key, 0, 2) == "__") {
                continue;
            }
            $i++;
            
            $lottery_currency_tab = [];
            if ($lottery['currency'] !== $manager_currency_tab['code']) {
                $lottery_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    $lottery['currency']        // In fact this is currency code!
                );
            }
            
            $price_tab = Lotto_Helper::get_price(
                $lottery,
                $lottery['model'],
                $lottery['tier'],
                $lottery['volume']
            );
            $cost_in_lottery_curr = $price_tab[0] + $price_tab[1];
            
            $user_cost_in_lottery_curr = Lotto_Helper::get_user_price($lottery);
            
            $final_income_in_lottery_curr = $user_cost_in_lottery_curr - $cost_in_lottery_curr;
            
            $whitelabel_margin_percentage = round($this->whitelabel['margin'] / 100, 4);
            $margin_in_lottery_curr = round(
                $final_income_in_lottery_curr * $whitelabel_margin_percentage,
                4
            );
            
            $lottery['name'] = _($lottery['name']);

            $is_enabled = $lottery['wis_enabled'];
            if ($lottery['is_temporarily_disabled']) {
                $is_enabled = false;
            }
            
            $lottery['wis_enabled_class'] = Lotto_View::show_boolean_class($is_enabled);
            
            $lottery['wis_enabled_value'] = Lotto_View::show_boolean($is_enabled);
            
            $lottery['model_text'] = _("Model") . ": " . $models[$lottery['model']];
            
            $expected_income_text = _("Expected income") . ": ";
            $lottery['expected_income_text'] = $expected_income_text;
            
            $expected_income = '';
            $expected_income_lottery = '';
            if ($lottery['income_type'] == Helpers_General::LOTTERY_INCOME_TYPE_CURRENCY) {
                if (!empty($lottery_currency_tab)) {
                    $expected_income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                        $lottery['income'],
                        $lottery_currency_tab,
                        $manager_currency_tab['code']
                    );
                    $expected_income = Lotto_View::format_currency(
                        $expected_income_manager,
                        $manager_currency_tab['code'],
                        true
                    );
                    
                    $income_lottery_formatted = Lotto_View::format_currency(
                        $lottery['income'],
                        $lottery['currency'],
                        true
                    );
                    $expected_income_lottery = _("Lottery currency") . ": " .
                        $income_lottery_formatted;
                } else {
                    $expected_income = Lotto_View::format_currency(
                        $lottery['income'],
                        $lottery['currency'],
                        true
                    );
                }
            } else {
                $income_divided = round($lottery['income'] / 100, 2);
                $expected_income .= Lotto_View::format_percentage(
                    $income_divided,
                    true
                );
            }
            $lottery['expected_income'] = $expected_income;
            $lottery['expected_income_lottery'] = $expected_income_lottery;
            
            $insured_tiers = '';
            if ((int)$lottery['model'] === Helpers_General::LOTTERY_MODEL_MIXED) {
                $insured_tiers .= _("Insured tiers") . ": ";
                $insured_tiers .= Lotto_View::format_number($lottery['tier']);
                $insured_tiers .= "<br>";
            }
            $lottery['insured_tiers'] = $insured_tiers;
            
            $current_cost_text = _("Current cost") . ": ";
            $lottery['current_cost_text'] = $current_cost_text;
            
            $current_cost = '';
            $current_cost_lottery = '';
            if (!empty($lottery_currency_tab)) {
                $current_cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $cost_in_lottery_curr,
                    $lottery_currency_tab,
                    $manager_currency_tab['code']
                );
                $current_cost = Lotto_View::format_currency(
                    $current_cost_manager,
                    $manager_currency_tab['code'],
                    true
                );
                
                $current_cost_formatted = Lotto_View::format_currency(
                    $cost_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
                $current_cost_lottery = _("Lottery currency") . ": " .
                    $current_cost_formatted;
            } else {
                $current_cost = Lotto_View::format_currency(
                    $cost_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
            }
            $lottery['current_cost'] = $current_cost;
            $lottery['current_cost_lottery'] = $current_cost_lottery;
            
            $current_price_text = _("Current price") . ": ";
            $lottery['current_price_text'] = $current_price_text;
            
            $current_price = '';
            $current_price_lottery = '';
            if (!empty($lottery_currency_tab)) {
                $current_price_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $user_cost_in_lottery_curr,
                    $lottery_currency_tab,
                    $manager_currency_tab['code']
                );
                $current_price = Lotto_View::format_currency(
                    $current_price_manager,
                    $manager_currency_tab['code'],
                    true
                );
                
                $current_price_formatted = Lotto_View::format_currency(
                    $user_cost_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
                $current_price_lottery = _("Lottery currency") . ": " .
                    $current_price_formatted;
            } else {
                $current_price = Lotto_View::format_currency(
                    $user_cost_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
            }
            $lottery['current_price'] = $current_price;
            $lottery['current_price_lottery'] = $current_price_lottery;
            
            $final_income = '';
            $final_income_lottery = '';
            if (!empty($lottery_currency_tab)) {
                $final_income_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $final_income_in_lottery_curr,
                    $lottery_currency_tab,
                    $manager_currency_tab['code']
                );
                $final_income = Lotto_View::format_currency(
                    $final_income_manager,
                    $manager_currency_tab['code'],
                    true
                );
                    
                $final_income_formatted = Lotto_View::format_currency(
                    $final_income_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
                $final_income_lottery = _("Lottery currency") . ": " .
                    $final_income_formatted;
            } else {
                $final_income = Lotto_View::format_currency(
                    $final_income_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
            }
            $lottery['final_income'] = $final_income;
            $lottery['final_income_lottery'] = $final_income_lottery;
            
            $asterix = '';
            if ((int)$lottery['model'] === Helpers_General::LOTTERY_MODEL_MIXED) {
                $this->show_asterisk = true;
                $asterix = '*';
            }
            $lottery['asterix'] = $asterix;
            
            $margin = '';
            $margin_lottery = '';
            if (!empty($lottery_currency_tab)) {
                $margin_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $margin_in_lottery_curr,
                    $lottery_currency_tab,
                    $manager_currency_tab['code']
                );
                $margin = Lotto_View::format_currency(
                    $margin_manager,
                    $manager_currency_tab['code'],
                    true
                );
                    
                $margin_lottery_formatted = Lotto_View::format_currency(
                    $margin_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
                $margin_lottery = _("Lottery currency") . ": " .
                    $margin_lottery_formatted;
            } else {
                $margin = Lotto_View::format_currency(
                    $margin_in_lottery_curr,
                    $lottery['currency'],
                    true
                );
            }
            $lottery['margin'] = $margin;
            $lottery['margin_lottery'] = $margin_lottery;
            
            $lottery['min_lines'] = Lotto_View::format_number($lottery['min_lines']);
            
            $lottery['edit_url'] = "lotterysettings/edit/" . ($key + 1);

            $whitelabel_lottery = Model_Whitelabel_Lottery::find_one_by([
                'lottery_id' => $lottery['id'],
                'whitelabel_id' => $this->whitelabel['id']
            ]);
            $quick_pick_lines = $whitelabel_lottery->quick_pick_lines;

            if (!$quick_pick_lines)
            {
                $quick_pick_lines = 3;
            }

            $lottery['quick_pick_lines'] = $quick_pick_lines;
            
            $prepared_lottery_data[] = $lottery;
        }
        
        return $prepared_lottery_data;
    }
}
