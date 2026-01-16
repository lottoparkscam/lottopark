<?php
use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Settings_Currency_Edit
 */
class Forms_Whitelabel_Settings_Currency_Edit extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel;
    
    /**
     *
     * @var string
     */
    private $redirect_path;
    
    /**
     *
     * @var View
     */
    private $inside;
    
    /**
     * For this array currencies have ID as a key
     *
     * @var array
     */
    private $kcurrencies;
    
    /**
     * For this array currencies have CODE as a key
     * @var array
     */
    private $vcurrencies;
    
    /**
     *
     * @var bool
     */
    private $full_form = true;
    
    /**
     *
     * @var int
     */
    private $edit_id = null;
    
    /**
     *
     * @var array
     */
    private $edit_data = null;
    
    /**
     *
     * @var array
     */
    private $errors = null;
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param string $path_to_view
     * @param string $redirect_path
     * @param int|null $edit_id
     */
    public function __construct(
        int $source,
        array $whitelabel,
        bool $should_check_whitelabel,
        string $path_to_view,
        string $redirect_path,
        int $edit_id = null
    ) {
        $this->source = $source;
        
        $this->inside = Presenter::forge($path_to_view);
        $this->whitelabel = $whitelabel;
        $this->redirect_path = $redirect_path;
        
        if (isset($edit_id) && $edit_id > 0) {
            $this->edit_id = $edit_id;
            $edit_data = Model_Whitelabel_Default_Currency::get_row_with_currency_data($edit_id);
            if ($edit_data !== null) {
                $this->edit_data = $edit_data;
            }
        }
        
        $whitelabel_default_currencies = Model_Whitelabel_Default_Currency::get_all_by_whitelabel($this->whitelabel);
        
        // Currencies already choosen should be removed from list of available currencies
        $already_saved_currencies = [];
        foreach ($whitelabel_default_currencies as $key => $saved_currency) {
            if (!isset($this->edit_data) ||
                (isset($this->edit_data) &&
                    $this->edit_data['currency_id'] !== $saved_currency['currency_id'])
            ) {
                $already_saved_currencies[] = $saved_currency['currency_code'];
            }
        }
        
        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        $vcurrencies = [];
        foreach ($currencies as $currency) {
            if (!in_array($currency['code'], $already_saved_currencies)) {
                $kcurrencies[$currency['id']] = $currency['code'];
                $vcurrencies[$currency['code']] = $currency;
            }
        }
        asort($kcurrencies);
        ksort($vcurrencies);
        
        $this->kcurrencies = $kcurrencies;
        $this->vcurrencies = $vcurrencies;
        
        if ($source == Helpers_General::SOURCE_ADMIN) {
            $this->full_form = true;
        } elseif ($should_check_whitelabel) {
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                $this->full_form = true;
            } else {
                $this->full_form = false;
            }
        }
    }
    
    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return string
     */
    public function get_redirect_path(): string
    {
        return $this->redirect_path;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @return array
     */
    public function get_kcurrencies(): array
    {
        return $this->kcurrencies;
    }
    
    /**
     *
     * @return array
     */
    public function get_vcurrencies(): array
    {
        return $this->vcurrencies;
    }
    
    /**
     *
     * @return bool
     */
    public function get_full_form(): bool
    {
        return $this->full_form;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        
        $val->add("input.site_currency", _("Defined currency"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric");
        
        $val->add("input.first_box_deposit", _("Default deposit for first box"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        $val->add("input.second_box_deposit", _("Default deposit for second box"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        $val->add("input.third_box_deposit", _("Default deposit for third box"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        if ($this->get_full_form()) {
            $val->add("input.min_purchase_amount", _("Minimum Payment by Currency"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);

            $val->add("input.min_deposit_amount", _("Minimum Deposit by Currency"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);

            $val->add("input.min_withdrawal", _("Minimum Withdrawal by Currency"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);

            $val->add("input.max_order_amount", _("Maximum Order Amount by Currency"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);
            
            $val->add("input.max_deposit_amount", _("Maximum Deposit Amount by Currency"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);
        }
        
        if (isset($this->edit_data) &&
                (int)$this->edit_data['is_default_for_site'] === 0 ||
            !isset($this->edit_data)
        ) {
            $val->add("input.is_default_for_site", _("Make default for site"))
                ->add_rule("trim")
                ->add_rule("match_value", 1);
        }
        
        return $val;
    }

    /**
     *
     * @param float $value
     * @param array $currency_tab
     * @param string $input_name
     * @param float $already_converted
     * @return array
     */
    private function get_single_field_data(
        $value,
        $currency_tab,
        $input_name,
        $already_converted = null
    ): array {
        if (null !== Input::post($input_name)) {
            $value = Input::post($input_name);
        }
        
        $record = [];
        
        if (empty($already_converted)) {
            $value_converted = Helpers_Currency::get_single_converted_from_currency(
                $currency_tab,
                $value
            );
            $record = [
                'original' => $value,
                'in_gateway_currency' => $value_converted,
                'default_in_gateway' => $value_converted
            ];
        } else {
            $record = [
                'original' => $value,
                'in_gateway_currency' => $already_converted,
                'default_in_gateway' => $already_converted
            ];
        }
        
        return $record;
    }
    
    /**
     *
     * @return void
     */
    private function set_inside_values(): void
    {
        $source = $this->get_source();
        $whitelabel = $this->get_whitelabel();
        
        $title_text = _("Add currency");
        $main_help_block_text = _("Here you can add currency to be available for users on site.");
        $button_submit_text = _("Submit");
        $results = null;
        
        $default_currency_tab = Helpers_Currency::get_mtab_currency(true);
        
        // At this moment default system currency is EUR
        // But soon will be changed
        $this->inside->set("default_system_currency_id", $default_currency_tab['id']);
        $this->inside->set("gateway_currency_rate", $default_currency_tab['rate']);
        $this->inside->set("multiplier_in_usd", $default_currency_tab['multiplier_in_usd']);
        $this->inside->set("currencies", $this->vcurrencies);
        
        // This value is needed for deposit boxes
        $first_from_currencies = reset($this->vcurrencies);
        $first_from_currencies['converted_multiplier'] = 0.00;
        
        $this->inside->set("first_from_currencies", $first_from_currencies);
        
        // This variable causes only to hide by HTML
        // the list of currencies in the case
        // of edit action for manager
        $show_list_of_currencies = true;
        
        $min_payment_record = [];
        $min_deposit_record = [];
        $min_withdrawal_record = [];
        $max_order_amount_record = [];
        $max_deposit_amount_record = [];
        if (!isset($this->edit_data)) {
            $results = Helpers_Currency::get_default_deposits_in_currency($first_from_currencies);
        
            $min_payment_record = $this->get_single_field_data(
                '0.00',
                $first_from_currencies,
                "input.min_purchase_amount"
            );
            $min_deposit_record = $this->get_single_field_data(
                '0.00',
                $first_from_currencies,
                "input.min_deposit_amount"
            );
            
            $min_withdrawal_default = '10.00';
            $max_order_amount_default = '1000.00';
            $max_deposit_amount_default = '1000.00';
            
            list(
                $min_withdrawal_converted,
                $min_withdrawal_in_gateway_currency
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_currency_tab['multiplier_in_usd'],
                $first_from_currencies,
                $min_withdrawal_default,
                true
            );
            
            list(
                $max_order_amount_converted,
                $max_order_amount_in_gateway_currency
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_currency_tab['multiplier_in_usd'],
                $first_from_currencies,
                $max_order_amount_default,
                true
            );
            
            list(
                $max_deposit_amount_converted,
                $max_deposit_amount_in_gateway_currency
            ) = Helpers_Currency::get_single_converted_from_system_currency(
                $default_currency_tab['multiplier_in_usd'],
                $first_from_currencies,
                $max_deposit_amount_default,
                true
            );
            
            $min_withdrawal_record = $this->get_single_field_data(
                $min_withdrawal_converted,
                $first_from_currencies,
                "input.min_withdrawal",
                $min_withdrawal_in_gateway_currency
            );
            
            $max_order_amount_record = $this->get_single_field_data(
                $max_order_amount_converted,
                $first_from_currencies,
                "input.max_order_amount",
                $max_order_amount_in_gateway_currency
            );
            
            $max_deposit_amount_record = $this->get_single_field_data(
                $max_deposit_amount_converted,
                $first_from_currencies,
                "input.max_deposit_amount",
                $max_deposit_amount_in_gateway_currency
            );
        } else {
            $title_text = _("Edit currency");
            $main_help_block_text = _("Here you can edit current currency to be available for users on site.");
            $button_submit_text = _("Update");
            
            if ($source !== Helpers_General::SOURCE_ADMIN) {
                // Only in that case list of currencies should be hidden
                $show_list_of_currencies = false;
            }
            
            $this->inside->set("edit_data", $this->edit_data);
            
            $from_currency_tab = [
                'id' => $this->edit_data['currency_id'],
                'code' => $this->edit_data['currency_code'],
                'rate' => $this->edit_data['rate']
            ];

            $results = Helpers_Currency::get_default_deposits_from_currency(
                $from_currency_tab,
                $this->edit_data
            );
            
            $min_payment_record = $this->get_single_field_data(
                $this->edit_data['min_purchase_amount'],
                $from_currency_tab,
                "input.min_purchase_amount"
            );
            $min_deposit_record = $this->get_single_field_data(
                $this->edit_data['min_deposit_amount'],
                $from_currency_tab,
                "input.min_deposit_amount"
            );
            $min_withdrawal_record = $this->get_single_field_data(
                $this->edit_data['min_withdrawal'],
                $from_currency_tab,
                "input.min_withdrawal"
            );
            $max_order_amount_record = $this->get_single_field_data(
                $this->edit_data['max_order_amount'],
                $from_currency_tab,
                "input.max_order_amount"
            );
            $max_deposit_amount_record = $this->get_single_field_data(
                $this->edit_data['max_deposit_amount'],
                $from_currency_tab,
                "input.max_deposit_amount"
            );
        }

        $show_default_tickbox = false;
        $is_default_checked = '';
        if (isset($this->edit_data) &&
                (int)$this->edit_data['is_default_for_site'] === 0 ||
            !isset($this->edit_data)
        ) {
            $show_default_tickbox = true;
            if ((null !== Input::post("input.is_default_for_site") &&
                    (int)Input::post("input.is_default_for_site") === 1)
            ) {
                $is_default_checked = ' checked="checked"';
            }
        }
        
        $this->inside->set("show_list_of_currencies", $show_list_of_currencies);
        
        $this->inside->set("show_default_tickbox", $show_default_tickbox);
        $this->inside->set("is_default_checked", $is_default_checked);
        
        $this->inside->set("full_form", $this->get_full_form());
        
        $this->inside->set("title_text", $title_text);
        $this->inside->set("main_help_block_text", $main_help_block_text);
        
        $this->inside->set("button_submit_text", $button_submit_text);
        $this->inside->set("results", $results);
        
        $this->inside->set("min_payment_record", $min_payment_record);
        $this->inside->set("min_deposit_record", $min_deposit_record);
        $this->inside->set("min_withdrawal_record", $min_withdrawal_record);
        $this->inside->set("max_order_amount_record", $max_order_amount_record);
        $this->inside->set("max_deposit_amount_record", $max_deposit_amount_record);
        
        $default_currency_text = _("Value in ");
        $formatted_currecy = Lotto_View::format_currency_code($default_currency_tab['code']);
        $default_currency_text .= $formatted_currecy;
        $this->inside->set("default_currency_text", $default_currency_text);
        
        $this->inside->set("whitelabel", $whitelabel);
    }

    /**
     *
     * @return array
     */
    private function prepare_errors_tab()
    {
        $fields = [
            'first_box_deposit' => '',
            'second_box_deposit' => '',
            'third_box_deposit' => '',
        ];
        
        if ($this->get_full_form()) {
            $fields['min_purchase_amount'] = '';
            $fields['min_deposit_amount'] = '';
            $fields['min_withdrawal'] = '';
            $fields['max_order_amount'] = '';
            $fields['max_deposit_amount'] = '';
        }
        
        if (isset($this->edit_data) &&
                (int)$this->edit_data['is_default_for_site'] === 0 ||
            !isset($this->edit_data)
        ) {
            $fields['is_default_for_site'] = '';
        }
        
        foreach ($fields as $key => $field) {
            if (isset($this->errors['input.' . $key])) {
                $fields[$key] = ' has-error';
            }
        }
        
        $this->inside->set("error_fields", $fields);
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->set_inside_values();
        
        $whitelabel = $this->get_whitelabel();
        $redirect_path = $this->get_redirect_path();
        $kcurrencies = $this->get_kcurrencies();
        
        $val = $this->validate_form();
        
        if ($val->run()) {
            $site_currency_id = $val->validated("input.site_currency");

            $is_manager = Helpers_General::is_manager();
            
            if ($is_manager &&
                !empty($this->edit_data['currency_id']) &&
                !empty($site_currency_id) &&
                (int)$site_currency_id !== (int)$this->edit_data['currency_id']
            ) {
                $errors = ["input.site_currency" => _("Incorrect currency.")];
                $this->errors = $errors;
                $this->inside->set("errors", $errors);
                $this->prepare_errors_tab();
                return ;
            }
            
            if (!empty($site_currency_id) &&
                !in_array($site_currency_id, array_keys($kcurrencies))
            ) {
                $errors = ["input.site_currency" => _("Incorrect currency.")];
                $this->errors = $errors;
                $this->inside->set("errors", $errors);
                $this->prepare_errors_tab();
                return;
            }
            
            if (!isset($this->edit_id)) {
                $count_default_currency = Model_Whitelabel_Default_Currency::count_for_whitelabel(
                    $whitelabel,
                    $site_currency_id
                );

                if ($count_default_currency > 0) {
                    Session::set_flash("message", ["danger", _("Currency record already exist in DB!")]);
                    Response::redirect($redirect_path);
                }
            }
            
            try {
                DB::start_transaction();
                
                $set = [
                    'currency_id' => $site_currency_id,
                    'default_deposit_first_box' => $val->validated("input.first_box_deposit"),
                    'default_deposit_second_box' => $val->validated("input.second_box_deposit"),
                    'default_deposit_third_box' => $val->validated("input.third_box_deposit")
                ];

                if ((isset($this->edit_data) &&
                    (int) $this->edit_data['is_default_for_site'] === 0 ||
                    !isset($this->edit_data)) &&
                    (int) $val->validated("input.is_default_for_site") === 1
                ) {
                    $old_default_currency_t = Model_Whitelabel_Default_Currency::find_by([
                        "whitelabel_id" => $whitelabel["id"],
                        "is_default_for_site" => 1
                    ]);

                    if (isset($old_default_currency_t[0]) &&
                        count($old_default_currency_t) > 0
                    ) {
                        $old_default_currency = $old_default_currency_t[0];
                        $set_old = [
                            "is_default_for_site" => 0
                        ];
                        $old_default_currency->set($set_old);
                        $old_default_currency->save();
                        $set['is_default_for_site'] = 1;
                    }
                }

                $successful_message = "";
                if (!isset($this->edit_id)) {
                    $whitelabel_default_currency = Model_Whitelabel_Default_Currency::forge();
                    $set['whitelabel_id'] = $whitelabel['id'];
                    $successful_message = _("Currency record successfully added!");
                } else {
                    $whitelabel_default_currency = Model_Whitelabel_Default_Currency::find_by_pk($this->edit_data['id']);
                    $successful_message = _("Currency record successfully updated!");
                }

                if ($this->get_full_form()) {
                    $set['min_purchase_amount'] = $val->validated("input.min_purchase_amount");
                    $set['min_deposit_amount'] = $val->validated("input.min_deposit_amount");
                    $set['min_withdrawal'] = $val->validated("input.min_withdrawal");
                    $set['max_order_amount'] = $val->validated("input.max_order_amount");
                    $set['max_deposit_amount'] = $val->validated("input.max_deposit_amount");
                } else {
                    $set['min_purchase_amount'] = $this->edit_data['min_purchase_amount'];
                    $set['min_deposit_amount'] = $this->edit_data['min_deposit_amount'];
                    $set['min_withdrawal'] = $this->edit_data['min_withdrawal'];
                    $set['max_order_amount'] = $this->edit_data['max_order_amount'];
                    $set['max_deposit_amount'] = $this->edit_data['max_deposit_amount'];
                }

                $whitelabel_default_currency->set($set);
                $whitelabel_default_currency->save();

                DB::commit_transaction();
                
                Session::set_flash("message", ["success", $successful_message]);
                Response::redirect($redirect_path);
            } catch (Exception $e) {
                DB::rollback_transaction();
                
                Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
                Response::redirect($redirect_path);
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->errors = $errors;
            $this->inside->set("errors", $errors);
        }
        
        $this->prepare_errors_tab();
    }
}
