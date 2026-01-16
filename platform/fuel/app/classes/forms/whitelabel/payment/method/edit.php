<?php

use Fuel\Core\Validation;
use Modules\Payments\PaymentFacadeLocator;
use Services\Logs\FileLoggerService;

/**
 * Class for preparing Forms_Whitelabel_Payment_Edit form
 */
class Forms_Whitelabel_Payment_Method_Edit extends Forms_Main
{
    use Controller_Trait_Payment_Validator,
        Controller_Trait_Payment_Data;
    
    const RESULT_CURRENCY_SUPPORTED = 100;
    const RESULT_CURRENCY_NOT_SUPPORTED = 200;
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var View
     */
    private $inside = null;

    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $currencies_indexed_by_id = [];
    
    /**
     *
     * @var array
     */
    private $payment_methods_list_counted = [];
    
    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_with_currency = [];
    
    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_indexed = [];

    /**
     *
     * @var array
     */
    private $languages = [];
    
    /**
     *
     * @var array
     */
    private $languages_indexed_by_id = [];
    
    /**
     *
     * @var int
     */
    private $edit_lp = null;
    
    /**
     *
     * @var null|array
     */
    private $payment_currency = null;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_with_currency
     * @param array $whitelabel_payment_methods_indexed
     * @param array $currencies_indexed_by_id
     * @param array $languages
     * @param array $languages_indexed_by_id
     */
    public function __construct(
        int $source,
        array $whitelabel,
        array $whitelabel_payment_methods_with_currency,
        array $whitelabel_payment_methods_indexed,
        array $currencies_indexed_by_id,
        array $languages,
        array $languages_indexed_by_id
    ) {
        $this->source = $source;
        $this->whitelabel = $whitelabel;
        
        $this->whitelabel_payment_methods_with_currency = $whitelabel_payment_methods_with_currency;
        $this->whitelabel_payment_methods_indexed = $whitelabel_payment_methods_indexed;
        
        $this->currencies_indexed_by_id = $currencies_indexed_by_id;
        
        $this->languages = $languages;
        $this->languages_indexed_by_id = $languages_indexed_by_id;

        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $match_collection = array_keys($this->currencies_indexed_by_id);
        
        $validation->add("input.language", _("Language"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric");
        
        $validation->add("input.method", _("Integrated Method"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric");
        
        $validation->add("input.name", _("Name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 100);

        $validation->add("input.only_deposit", _("Only deposit"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.show", _("Show on payment page"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.cost_percent", _("Percentage cost"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 99.99);
        
        $validation->add("input.cost_fixed", _("Fixed cost"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 999999999.99);
        
        $validation->add("input.cost_currency", _("Cost currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("match_collection", $match_collection);
        
        $validation->add("input.show_payment_logotype", _("Show payment logotype"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.custom_logotype", _("Custom logotype"))
            ->add_rule("trim")
            ->add_rule("max_length", 2083)
            ->add_rule("valid_url");

        $validation->add('input.allow_user_to_select_currency', 'Allow user to select currency on payment page')
            ->add_rule('trim')
            ->add_rule('match_value', 1);

        // This will be seen only for add new payment method case
        if (!isset($this->edit_lp)) {
            $validation->add("input.payment_currency", _("Payment currency"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("match_collection", $match_collection);

            $validation->add("input.min_purchase_by_currency", _("Minimum purchase by currency"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0);
        }
            
        return $validation;
    }
    
    /**
     *
     * @param string $action
     * @param int $token
     * @return array
     */
    private function get_edit_data(string $action, int $token = null): array
    {
        $edit = null;
        $data = [];
        
        $this->edit_lp = null;
        
        $edit_data = [
            $edit,
            $data,
            null
        ];
        
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            if ((string)$action !== "edit" ||
            ((string)$action === "edit" &&
                empty($token))
            ) {
                return $edit_data;
            }
            
            $edit_pk = (int)$token;
        } else {
            if ((string)$action !== "edit" ||
                ((string)$action === "edit" &&
                    !isset($this->whitelabel_payment_methods_indexed[$token - 1]))
            ) {
                return $edit_data;
            }
            
            $edit_pk = (int)$this->whitelabel_payment_methods_indexed[$token - 1]['id'];
        }
        
        $this->edit_lp = $token;
        
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($edit_pk);

        if ($whitelabel_payment_method !== null &&
            (int)$whitelabel_payment_method->whitelabel_id === (int)$this->whitelabel['id']
        ) {
            $edit = $whitelabel_payment_method;

            $data = !empty($whitelabel_payment_method->data_json) ? json_decode($whitelabel_payment_method->data_json, true) : unserialize($whitelabel_payment_method->data);
        }
        
        return [
            $edit,
            $data,
            $whitelabel_payment_method
        ];
    }
    
    /**
     *
     * @param Validation $validation
     * @param int $payment_currency_id
     * @return int
     */
    private function check_is_supported_currency(
        Validation $validation,
        int $payment_currency_id
    ): int {
        $is_currency_supported = $this->is_currency_supported(
            $validation,
            $payment_currency_id
        );
        
        // This variable will be used in other place as well
        $this->payment_currency = Helpers_Currency::get_mtab_currency(
            false,
            "",
            $payment_currency_id
        );
        
        if (!$is_currency_supported) {
            $error_msg = _(
                "Currency " . $this->payment_currency["code"] .
                " is not supported by that payment method!"
            );
            $errors = ['input.payment_currency' => $error_msg];
            $this->inside->set("errors", $errors);

            return self::RESULT_CURRENCY_NOT_SUPPORTED;
        }
        
        return self::RESULT_CURRENCY_SUPPORTED;
    }
    
    /**
     *
     * @param array $methods_list
     * @param Model_Whitelabel_Payment_Method $edit
     * @return int
     */
    private function main_process(
        array $methods_list,
        Model_Whitelabel_Payment_Method $edit = null
    ): int {
        if (null === Input::post("input.name")) {
            return self::RESULT_WITH_ERRORS;
        }
        
        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $payment_method_id = $validated_form->validated("input.method");
            $lang_id = $validated_form->validated("input.language");
            
            $additional_values_validation = null;
            
            if ((int)$payment_method_id > Helpers_Payment_Method::TEST) {
                $additional_values_validation = $this->get_payment_method_validation($validated_form);
            }

            if (!($additional_values_validation == null ||
                $additional_values_validation->run())
            ) {
                $errors = Lotto_Helper::generate_errors($additional_values_validation->error());
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }

            // This value is important only in the case of new action
            $payment_currency_id = null;
            if (!isset($edit['id'])) {
                $payment_currency_id = $validated_form->validated("input.payment_currency");
                $is_currency_supported = $this->check_is_supported_currency(
                    $validated_form,
                    $payment_currency_id
                );
                
                if ($is_currency_supported === self::RESULT_CURRENCY_NOT_SUPPORTED) {
                    return self::RESULT_WITH_ERRORS;
                }
            } else {
                // TODO: This option should be correlated with FRONT-END
                // It should show payment_currency and min_purchase input
                if ((int)$edit['payment_method_id'] !== (int)$payment_method_id) {
                    $payment_currency_id = (int)$edit['payment_currency_id'];
                    $is_currency_supported = $this->check_is_supported_currency(
                        $validated_form,
                        $payment_currency_id
                    );
                    
                    if ($is_currency_supported === self::RESULT_CURRENCY_NOT_SUPPORTED) {
                        return self::RESULT_WITH_ERRORS;
                    }
                }
            }
            
            if (!((int)$payment_method_id === 0 ||
                isset($methods_list[$payment_method_id]))
            ) {
                $errors = ['input.method' => _("Wrong method!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
                
            if (!isset($this->languages_indexed_by_id[$lang_id])) {
                $errors = ['input.language' => _("Wrong language!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
            
            $data = $this->get_payment_additional_data(
                $validated_form,
                $additional_values_validation
            );

            $order = Model_Whitelabel_Payment_Method::count(
                'id',
                null,
                [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "language_id" => $lang_id
                ]
            );

            $cost_percent = 0;
            if (!empty($validated_form->validated("input.cost_percent"))) {
                $cost_percent = $validated_form->validated("input.cost_percent");
            }

            $cost_fixed = 0;
            $cost_currency_id = null;
            if (!empty($validated_form->validated("input.cost_fixed"))) {
                $cost_fixed = $validated_form->validated("input.cost_fixed");
                $cost_currency_id = $validated_form->validated("input.cost_currency");
            }

            $show = 0;
            if ((int)$validated_form->validated("input.show") === 1) {
                $show = 1;
            }

            $only_deposit = 0;
            if ((int)$validated_form->validated("input.only_deposit") === 1) {
                $only_deposit = 1;
            }
            
            $show_payment_logotype = 0;
            if ((int)$validated_form->validated("input.show_payment_logotype") === 1) {
                $show_payment_logotype = 1;
            }
            
            $custom_logotype = "";
            if (!empty($validated_form->validated("input.custom_logotype"))) {
                $custom_logotype = $validated_form->validated("input.custom_logotype");
            }

            $allowUserToSelectCurrency = (int)$validated_form->validated('input.allow_user_to_select_currency') === 1;

            $set = [
                'whitelabel_id' => $this->whitelabel['id'],
                'payment_method_id' => $payment_method_id,
                'language_id' => $lang_id,
                'name' => $validated_form->validated("input.name"),
                'show' => $show,
                'data' => serialize($data),
                'data_json' => json_encode($data),
                'cost_percent' => $cost_percent,
                'cost_fixed' => $cost_fixed,
                'cost_currency_id' => $cost_currency_id,
                'show_payment_logotype' => $show_payment_logotype,
                'custom_logotype' => $custom_logotype,
                'allow_user_to_select_currency' => $allowUserToSelectCurrency,
            ];

            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) || (int)$this->source === Helpers_General::SOURCE_ADMIN) {
                $set['only_deposit'] = $only_deposit;
            }
            
            try {
                DB::start_transaction();
                
                $method = null;
                if (isset($edit['id'])) {
                    $method = $edit;
                } else {
                    $method = Model_Whitelabel_Payment_Method::forge();
                    $set['payment_currency_id'] = (int) $payment_currency_id;
                    $set['order'] = (int) $order + 1;
                }

                $method->set($set);
                $method->save();

                // This should be added after add new method
                if (empty($edit)) {
                    $zero_decimal_value = Model_Payment_Method_Supported_Currency::get_zero_decimal_value(
                        (int)$validated_form->validated("input.method"),
                        (string)$this->payment_currency["code"]
                    );
                    
                    $whitelabel_payment_method_id = (int) $method->id;

                    $whitelabel_payment_method_currency = Model_Whitelabel_Payment_Method_Currency::forge();

                    $set_new_currency = [
                        "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                        "currency_id" => (int) $payment_currency_id,
                        "is_zero_decimal" => $zero_decimal_value,
                        "is_enabled" => 1,
                        "is_default" => 1,
                        'min_purchase' => $validated_form->validated("input.min_purchase_by_currency")
                    ];

                    $whitelabel_payment_method_currency->set($set_new_currency);
                    $whitelabel_payment_method_currency->save();
                }
                DB::commit_transaction();
            } catch (Exception $e) {
                DB::rollback_transaction();

                $this->fileLoggerService->error(
                    $e->getMessage()
                );
                
                $errors = ['input.method' => _("There is a problem with database! Please contact us!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $whitelabel_payment_method Could be null in the case of 'new' action
     * @return array
     */
    private function get_default_currency(
        Model_Whitelabel_Payment_Method $whitelabel_payment_method = null
    ): array {
        $found = false;
        $default_currency_tab = [];
        
        // For edit
        if (isset($whitelabel_payment_method)) {
            $edit_pk = (int)$whitelabel_payment_method->id;
            
            $default_payment_method_currency_temp = Model_Whitelabel_Payment_Method_Currency::find_by([
                "whitelabel_payment_method_id" => $edit_pk,
                "is_default" => 1
            ]);

            if (isset($default_payment_method_currency_temp[0])) {
                $default_payment_method_currency = $default_payment_method_currency_temp[0];
                $default_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    (int)$default_payment_method_currency['currency_id']
                );
                $found = true;
            }
        }
            
        // As fallback currency or for 'new' action
        if (!$found) {
            $default_currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                "EUR"
            );
        }
        
        return $default_currency_tab;
    }
    
    /**
     *
     * @param string $action
     * @param string $template_path
     * @param int $token
     * @return int
     */
    public function process_form(
        string $action,
        string $template_path,
        int $token = null
    ): int {
        $methods_list = Model_Payment_Method::get_payment_methods();
        
        list(
            $edit,
            $data,
            $whitelabel_payment_method
        ) = $this->get_edit_data($action, $token);

        if ($action === "edit" && (is_null($whitelabel_payment_method) ||
            (!is_null($whitelabel_payment_method) &&
            (int)$whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id']))
        ) {
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        $this->inside = Presenter::forge($template_path);
        $this->inside->set("currencies", $this->currencies_indexed_by_id);

        Config::load("platform", true);
        $platform_ip = Config::get("platform.ip.whitelist")[0];
        $this->inside->set("platform_ip", $platform_ip);

        $default_currency_tab = $this->get_default_currency($whitelabel_payment_method);
        
        $this->inside->set("default_currency_id", $default_currency_tab['id']);
        
        $min_purchase_currency_id = (int) $default_currency_tab['id'];
        if (isset($edit['payment_currency_id'])) {
            $min_purchase_currency_id = (int) $edit['payment_currency_id'];
        } elseif (!is_null(Input::post("input.payment_currency"))) {
            $min_purchase_currency_id = (int) Input::post("input.payment_currency");
        }
        
        $payment_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            "",
            $min_purchase_currency_id
        );
        $this->inside->set("min_purchase_currency_code", $payment_currency_tab['code']);
        
        $result = $this->main_process($methods_list, $edit);
        
        if ($result !== 0) {
            if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
                $this->inside->set("whitelabel", $this->whitelabel);
            }
            $this->inside->set("data", $data);
            $this->inside->set("edit", $edit);
            $this->inside->set("edit_lp", $this->edit_lp);
            $this->inside->set("methods", $methods_list);
            $this->inside->set("cmethods", $this->payment_methods_list_counted);
            $this->inside->set("wmethods", $this->whitelabel_payment_methods_with_currency);
            $this->inside->set("langs", $this->languages);
        }
        
        return $result;
    }
}
