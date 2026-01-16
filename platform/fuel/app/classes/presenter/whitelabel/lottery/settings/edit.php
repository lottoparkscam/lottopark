<?php

/**
 * Description of Presenter_Whitelabel_Lottery_Settings_Edit
 */
class Presenter_Whitelabel_Lottery_Settings_Edit extends Presenter_Presenter
{
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $this->prepare_lottery_data();
        $prepared_type_data = $this->prepare_type_data();
        $this->set("type_data", $prepared_type_data);
    }
    
    /**
     *
     * @return void
     */
    private function prepare_lottery_data(): void
    {
        $model_names = [
            Helpers_General::LOTTERY_MODEL_PURCHASE => _("Purchase"),
            //Helpers_General::LOTTERY_MODEL_MIXED => _("Mixed (Insurance/Purchase)"),
            //Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN => _("Purchase + Scan")
        ];
        
        if (Helpers_General::ticket_scan_availability($this->whitelabel, $this->lottery, true)) {
            $model_names[Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN] = _("Purchase + Scan");
        }
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
            Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $model_names[Helpers_General::LOTTERY_MODEL_NONE] = _("None");
        }
        $this->set("model_names", $model_names);

        $model_error_class = '';
        if (isset($this->errors['input.model'])) {
            $model_error_class = ' has-error';
        }
        $this->set("model_error_class", $model_error_class);
        
        $income_error_class = '';
        if (isset($this->errors['input.income'])) {
            $income_error_class = ' has-error';
        }
        $this->set("income_error_class", $income_error_class);
        
        $income_value_t = $this->lottery['income'];
        if (null !== Input::post("input.income")) {
            $income_value_t = Input::post("input.income");
        }
        $income_value = Security::htmlentities($income_value_t);
        $this->set("income_value", $income_value);
        
        $lottery_currency_code = Lotto_View::format_currency_code($this->lottery['currency']);
        $income_types = [
            Helpers_General::LOTTERY_INCOME_TYPE_CURRENCY => $lottery_currency_code,
            Helpers_General::LOTTERY_INCOME_TYPE_PERCENT => "%"
        ];
        $this->set("income_types", $income_types);
        
        $insured_hidden_class = '';
        if ((Input::post("model") !== null &&
                Input::post("model") != Helpers_General::LOTTERY_MODEL_MIXED) ||
            (Input::post("model") == null &&
                $this->lottery['model'] != Helpers_General::LOTTERY_MODEL_MIXED)
        ) {
            $insured_hidden_class = ' hidden';
        }
        $this->set("insured_hidden_class", $insured_hidden_class);

        $help_block_text_part = _(
            "You will have to cover the winnings from " .
            "uninsured prize tiers. They are included " .
            "in the calculations below."
        );
        $margin_in_percentage = round($this->whitelabel['margin'] / 100, 2);
        $margin_in_formatted = Lotto_View::format_percentage($margin_in_percentage);
        $help_block_tiers = sprintf($help_block_text_part, $margin_in_formatted);
        $this->set("help_block_tiers", $help_block_tiers);
        
        $sample_calc_hidden_class = '';
        if ((Input::post("model") !== null &&
                Input::post("model") != Helpers_General::LOTTERY_MODEL_MIXED) ||
            (Input::post("model") == null &&
                $this->lottery['model'] != Helpers_General::LOTTERY_MODEL_MIXED)
        ) {
            $sample_calc_hidden_class = ' hidden';
        }
        $this->set("sample_calc_hidden_class", $sample_calc_hidden_class);

        $sample_calc_jackpot_error_class = '';
        if (isset($this->errors['input.jackpot'])) {
            $sample_calc_jackpot_error_class = ' has-error';
        }
        $this->set("sample_calc_jackpot_error_class", $sample_calc_jackpot_error_class);
        $sample_calc_jackpot_value_t = round($this->lottery['current_jackpot']);
        if (null !== Input::post("input.jackpot")) {
            $sample_calc_jackpot_value_t = Input::post("input.jackpot");
        }
        $sample_calc_jackpot_value = Security::htmlentities($sample_calc_jackpot_value_t);
        $this->set("sample_calc_jackpot_value", $sample_calc_jackpot_value);
        
        $sample_calc_volume_error_class = '';
        if (isset($this->errors['input.volume'])) {
            $sample_calc_volume_error_class = ' has-error';
        }
        $this->set("sample_calc_volume_error_class", $sample_calc_volume_error_class);
        
        $sample_calc_volume_value_t = $this->lottery['volume'];
        if (null !== Input::post("input.volume")) {
            $sample_calc_volume_value_t = Input::post("input.volume");
        }
        $sample_calc_volume_value = Security::htmlentities($sample_calc_volume_value_t);
        $this->set("sample_calc_volume_value", $sample_calc_volume_value);

				$price_formatted = $this->lottery['price'] + $this->lottery['fee'];
        $min_price_for_user = Lotto_View::format_currency(
            $price_formatted,
            $this->lottery['currency'],
            true
        );
        $this->set("min_price_for_user", $min_price_for_user);

        $lottery_status_checked = '';
        if ((null !== Input::post("input.enabled") &&
                Input::post("input.enabled") == 1) ||
            $this->lottery['wis_enabled'] == 1
        ) {
            $lottery_status_checked = ' checked="checked"';
        }
        $this->set("lottery_status_checked", $lottery_status_checked);

        $lottery_bonus_balance_checked = '';
        $is_bonus_balance_in_use = (
            Input::post("input.is_bonus_balance_in_use") !== null &&
                Input::post("input.is_bonus_balance_in_use") === "1"
        ) ||
            $this->lottery['wis_bonus_balance_in_use'] === "1";

        if ($is_bonus_balance_in_use) {
            $lottery_bonus_balance_checked = ' checked="checked"';
        }
        $this->set("lottery_bonus_balance_checked", $lottery_bonus_balance_checked);

        $bonus_balance_purchase_limit_per_user_error_class = '';
        if (isset($this->errors['input.bonusBalancePurchaseLimitPerUser'])) {
            $bonus_balance_purchase_limit_per_user_error_class = ' has-error';
        }
        $this->set("bonus_balance_purchase_limit_per_user_error_class", $bonus_balance_purchase_limit_per_user_error_class);

        $bonus_balance_purchase_limit_per_user_current_value = $this->lottery['bonus_balance_purchase_limit_per_user'];
        if (null !== Input::post("input.bonusBalancePurchaseLimitPerUser")) {
            $bonus_balance_purchase_limit_per_user_current_value = Input::post("input.bonusBalancePurchaseLimitPerUser");
        }
        $bonus_balance_purchase_limit_value = Security::htmlentities($bonus_balance_purchase_limit_per_user_current_value);
        $this->set("bonus_balance_purchase_limit_value", $bonus_balance_purchase_limit_value);

        $multidraws_status_checked = '';
        if ((null !== Input::post("input.multidraws_enabled") &&
                Input::post("input.multidraws_enabled") == 1) ||
            $this->lottery['multidraws_enabled'] == 1
        ) {
            $multidraws_status_checked = ' checked="checked"';
        }
        $this->set("multidraws_status_checked", $multidraws_status_checked);
        
        // check errors and return previous value to input field
        // input minlines
        $min_lines_error_class = '';
        if (isset($this->errors['input.minlines'])) {
            $min_lines_error_class = ' has-error';
        }
        $this->set("min_lines_error_class", $min_lines_error_class);
        
        $min_lines_value_t = $this->lottery['min_lines'];
        if (null !== Input::post("input.minlines")) {
            $min_lines_value_t = Input::post("input.minlines");
        }
        $min_lines_value = Security::htmlentities($min_lines_value_t);
        $this->set("min_lines_value", $min_lines_value);

        // check errors and return previous value to input field
        // input quick-pick
        $quick_pick_error_class = '';
        if (isset($this->errors['input.quick_pick_lines'])) {
            $quick_pick_error_class = ' has-error';
        }
        $this->set("quick_pick_lines_error_class", $quick_pick_error_class);

        /** @var Model_Whitelabel_Lottery $whitelabel_lottery */
        $whitelabel_lottery = Model_Whitelabel_Lottery::find_one_by([
            'lottery_id' => $this->lottery['id'],
            'whitelabel_id' => $this->whitelabel['id']
        ]);

        /** @var Model_Lottery_Provider $lottery_provider */
        $lottery_provider = Model_Lottery_Provider::find_by_pk($whitelabel_lottery['lottery_provider_id']);

        $multiplier = $lottery_provider->multiplier;

        $this->set("multiplier", $multiplier);

        $quick_pick_lines_value_t = $whitelabel_lottery->quick_pick_lines;
        if (null !== Input::post("input.quick_pick")) {
            $quick_pick_lines_value_t = Input::post("input.quick_pick_lines");
        }
        $quick_pick_lines_value = Security::htmlentities($quick_pick_lines_value_t);
        $quick_pick_lines_value = $quick_pick_lines_value == 0 ? 3 : $quick_pick_lines_value;
        $this->set("quick_pick_lines_value", $quick_pick_lines_value);
        
        $help_block_text_part = _(
            "The value has to be a multiple of " .
            "<span>X</span> due to our ticket provider's limits."
        );
        $multiplier_formatted = Lotto_View::format_number($this->lottery['multiplier']);
        $help_block_multipier = sprintf($help_block_text_part, $multiplier_formatted);
        $this->set("help_block_multipier", $help_block_multipier);

        if (isset($lottery_provider)) {
            $this->set('min_bets', $lottery_provider['min_bets']);
            $this->set('max_bets', $lottery_provider['max_bets']);
        }
    }

    /**
     *
     * @return array
     */
    private function prepare_type_data(): array
    {
        $prepared_type_data = [];
        
        foreach ($this->type_data as $key => $type) {
            $type_disabled_class = '';
            if ((Helpers_Whitelabel::is_V1($this->whitelabel['type']) &&
                    $key + 1 < $this->lottery_type['def_insured_tiers']) ||
                $type['type'] == Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK
            ) {
                $type_disabled_class = ' disabled';
            }
            
            $type_selected = '';
            if ((!empty(Input::post("insured_tiers")) &&
                    Input::post("insured_tiers") == $key + 1) ||
                (empty(Input::post("insured_tiers")) &&
                    $this->lottery['tier'] == $key + 1)
            ) {
                $type_selected = ' selected="selected"';
            }
            
            $type['option_attributes'] = $type_disabled_class . $type_selected;
            
            // Calculate of values for Insured Tiers
            $option_value = sprintf(_("Up to tier #%s"), Lotto_View::format_number($key + 1));
            $option_value .= " - ";
            $option_value .= _("Match");
            $option_value .= ": ";
            
            $option_value .= $type['match_n'];
            if ($this->lottery_type['bcount'] > 0 ||
                $this->lottery_type['bextra'] > 0
            ) {
                if ($this->lottery_type['bextra'] == 0 ||
                        ($this->lottery_type['bextra'] > 0 &&
                            $type['match_b'])
                ) {
                    $option_value .= " + ";
                }
                
                if ($this->lottery_type['bextra'] == 0 ||
                        ($this->lottery_type['bextra'] > 0 && $type['match_b'])) {
                    $option_value .= $type['match_b'];
                }
            }
            
            $option_value .= " - ";

            if ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                $option_value .= _("Free Quick Pick");
            } elseif ($type['is_jackpot']) {
                $option_value .= _("Jackpot");
            } elseif ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_ESTIMATED) {
                $option_value .= _("Estimated: ");
                $option_value .= Lotto_View::format_currency(
                    $type['estimated'],
                    $this->lottery['currency']
                );
            } elseif ($type['type'] == Helpers_General::LOTTERY_TYPE_DATA_PRIZE) {
                $option_value .= Lotto_View::format_currency(
                    $type['prize'],
                    $this->lottery['currency']
                );
            }
            $type['value'] = $option_value;
            
            $prepared_type_data[] = $type;
        }
        
        return $prepared_type_data;
    }
}
