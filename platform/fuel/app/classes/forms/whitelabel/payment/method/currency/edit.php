<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Whitelabel_Payment_Currency_Edit
 */
class Forms_Whitelabel_Payment_Method_Currency_Edit extends Forms_Main
{
    use Traits_Payment_Method_Currency;
    
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
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_indexed = [];
    
    /**
     *
     * @var array
     */
    private $currencies_indexed_by_id = [];

    /**
     *
     * @var Model_Whitelabel_Payment_Method_Currency
     */
    private $edit_data = null;
    
    /**
     *
     * @var bool
     */
    private $show_default_tickbox = false;
    
    /**
     *
     * @var int|null
     */
    private $edit_id = null;
    
    /**
     *
     * @var Model_Whitelabel_Payment_Method
     */
    private $whitelabel_payment_method = null;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed
     * @param array $currencies_indexed_by_id
     */
    public function __construct(
        int $source,
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        array $currencies_indexed_by_id
    ) {
        $this->source = $source;
        $this->whitelabel = $whitelabel;
        $this->whitelabel_payment_methods_indexed = $whitelabel_payment_methods_indexed;
        $this->currencies_indexed_by_id = $currencies_indexed_by_id;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function prepare_currencies_indexed_by_id(int $whitelabel_payment_method_id): void
    {
        $all_enabled_currencies = Model_Whitelabel_Payment_Method_Currency::find_by([
            "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
            "is_enabled" => 1
        ]);
        
        $currencies_keys = array_keys($this->currencies_indexed_by_id);
        
        foreach ($all_enabled_currencies as $key => $currency) {
            if (isset($this->edit_data) &&
                (int)$this->edit_data->currency_id === (int)$currency->currency_id &&
                (int)$this->edit_data->id === (int)$this->edit_id
            ) {
                continue ;
            }
            
            if (in_array((int)$currency->currency_id, $currencies_keys)) {
                unset($this->currencies_indexed_by_id[(int)$currency->currency_id]);
            }
        }
    }
    
    /**
     * @param array $kcurrencies Currancies codes
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        
        $match_collection = array_keys($this->currencies_indexed_by_id);
        
        $val->add("input.payment_currency_id", _("Payment currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("required")
            ->add_rule("match_collection", $match_collection);
        
        $val->add("input.min_purchase", _("Minimum purchase by currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0);
        
        $val->add("input.is_default", _("Make that currency default"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        return $val;
    }
    
    /**
     *
     * @return array
     */
    private function get_errors_tab()
    {
        $fields = [
            "payment_currency_id" => "",
            "min_purchase_by_currency" => ""
        ];
                
        if (isset($this->edit_data) &&
                (int)$this->edit_data->is_default === 0 ||
            !isset($this->edit_data)
        ) {
            $fields["is_default"] = '';
        }
        
        foreach ($fields as $key => $field) {
            if (isset($this->errors["input." . $key])) {
                $fields[$key] = ' has-error';
            }
        }
        
        $this->inside->set("error_fields", $fields);
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return int
     */
    private function main_process(int $whitelabel_payment_method_id): int
    {
        if (empty(Input::post())) {
            return self::RESULT_WITH_ERRORS;
        }
        
        $val = $this->validate_form();
        
        if ($val->run()) {
            $payment_currency_id = (int)$val->validated("input.payment_currency_id");
            // If this is 'new' action or 'edit' but currency which is requested to
            // add could be already in DB
            if (!isset($this->edit_data) ||
                (isset($this->edit_data) &&
                    (int)$this->edit_data->currency_id !== $payment_currency_id)
            ) {
                $whitelabel_payment_method_currency_enabled = Model_Whitelabel_Payment_Method_Currency::find_by([
                    "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                    "currency_id" => $payment_currency_id,
                    "is_enabled" => 1
                ]);

                if (isset($whitelabel_payment_method_currency_enabled[0])) {
                    $errors = ['input.method' => _("This currency is already added!")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }
            }
            
            $is_currency_supported = $this->is_currency_supported(
                (int)$this->whitelabel_payment_method->payment_method_id,
                $payment_currency_id
            );
            
            $payment_currency = Helpers_Currency::get_mtab_currency(
                false,
                "",
                $payment_currency_id
            );
            
            if (!$is_currency_supported) {
                $error_msg = _(
                    "Currency " . $payment_currency["code"] .
                    " is not supported!"
                );
                $errors = ['input.payment_currency_id' => $error_msg];
                $this->inside->set("errors", $errors);

                return self::RESULT_WITH_ERRORS;
            }
            
            if (isset($this->edit_data->min_purchase_currency_code)) {
                unset($this->edit_data->min_purchase_currency_code);
            }
            
            // All this stuff is so complicated
            // bacaue in DB there is is_enabled flag
            // and if we want to add new currency and
            // if it exists that flag is good and
            // will be used for such case - so it will be set $is_enabled
            // from 0 to 1 and will be updated
            // If there is no such currency, system will add new currency!
            try {
                DB::start_transaction();
                
                $min_purchase = $val->validated("input.min_purchase");
                $is_default = $val->validated("input.is_default");
                
                $zero_decimal_value = Model_Payment_Method_Supported_Currency::get_zero_decimal_value(
                    (int)$this->whitelabel_payment_method->payment_method_id,
                    (string)$payment_currency["code"]
                );
                
                $set = [
                    "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                    "currency_id" => $payment_currency_id,
                    "is_zero_decimal" => $zero_decimal_value,
                    "is_enabled" => 1,
                    "min_purchase" => $min_purchase
                ];

                // Try to find if there is a default currency already in DB
                // and if yes make it not default - this should be
                // only in the case that new or edited currency
                // is forced to be default
                if ($this->show_default_tickbox &&
                    !empty($is_default) &&
                    (int) $is_default === 1
                ) {
                    $whitelabel_payment_method_currency_default = Model_Whitelabel_Payment_Method_Currency::find_by([
                        "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                        "is_default" => 1
                    ]);

                    if (isset($whitelabel_payment_method_currency_default[0])) {
                        $whitelabel_payment_method_currency_no_default = $whitelabel_payment_method_currency_default[0];
                        $set_no_default = [
                            "is_default" => 0
                        ];
                        $whitelabel_payment_method_currency_no_default->set($set_no_default);
                        $whitelabel_payment_method_currency_no_default->save();
                    }

                    $set["is_default"] = 1;
                }

                $whitelabel_payment_method_currency = null;

                // Try to check if requested currency is already in DB
                // but is not enabled for the system - this record is returned
                // as array
                $whitelabel_payment_method_currency_disabled = Model_Whitelabel_Payment_Method_Currency::find_by([
                    "whitelabel_payment_method_id" => $whitelabel_payment_method_id,
                    "currency_id" => $payment_currency_id,
                    "is_enabled" => 0
                ]);

                // If there is so - not enabled exists
                if (isset($whitelabel_payment_method_currency_disabled[0])) {
                    $whitelabel_payment_method_currency = $whitelabel_payment_method_currency_disabled[0];
                    // 'edit' action and enabled = 0 exists
                    if (isset($this->edit_data)) {
                        // Set as object to make possible to update!
                        $whitelabel_payment_method_currency_old = $this->edit_data;
                        $set_for_old = [
                            "is_enabled" => 0
                        ];

                        if ((int)$this->edit_data->is_default === 1) {
                            $set_for_old["is_default"] = 0;
                            $set["is_default"] = 1;
                        }

                        $whitelabel_payment_method_currency_old->set($set_for_old);
                        $whitelabel_payment_method_currency_old->save();
                    }
                } elseif (isset($this->edit_data)) {    // For 'edit' action - enabled = 0 doesn't exist
                    $whitelabel_payment_method_currency = $this->edit_data;
                } else {                                // For totally 'new' entrance
                    $whitelabel_payment_method_currency = Model_Whitelabel_Payment_Method_Currency::forge();
                }

                // For default currency min_purchase value should be recalculated
                // and payment_currency should be changed
                if ((isset($set['is_default']) &&
                        (int)$set['is_default'] === 1) ||
                    (isset($this->edit_data) &&
                        isset($this->edit_data->is_default) &&
                        (int)$this->edit_data->is_default === 1 &&
                        (int)$this->edit_data->currency_id !== $payment_currency_id)
                ) {
                    // Update payment_currency_id for payment_method
                    $set_for_payment_method = [
                        'payment_currency_id' => $payment_currency_id
                    ];
                    $this->whitelabel_payment_method->set($set_for_payment_method);
                    $this->whitelabel_payment_method->save();
                    
                    Lotto_Helper::clear_cache('model_whitelabel_payment_method.paymentmethods.' . $this->whitelabel['id']);
                }
                
                // And after all changes currency row is updated/inserted
                $whitelabel_payment_method_currency->set($set);
                $whitelabel_payment_method_currency->save();
                
                DB::commit_transaction();
            } catch (\Exception $e) {
                DB::rollback_transaction();

                $this->fileLoggerService->error(
                    $e->getMessage()
                );
                
                $errors = ['input.payment_currency_id' => _("There is a problem with database! Please contact us!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_index Index for data within kmethods array
     * (for WHITELABEL, but for ADMIN this is strictly equal of the $whitelabel_payment_method_id)
     * @param string $template_path path to template
     * @param int $edit_id Could be null in the case that is add
     * @return int
     */
    public function process_form(
        int $whitelabel_payment_method_index,
        string $template_path,
        int $edit_id = null
    ): int {
        $whitelabel_payment_method_id = 0;
        $current_whitelabel_payment_method_index = 0;
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $whitelabel_payment_method_id = $whitelabel_payment_method_index;
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_index;
        } else {
            $whitelabel_payment_method_id = $this->whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
        }
        
        $this->whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);
        
        if ($this->whitelabel_payment_method === null ||
            (int)$this->whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id']
        ) {
            $this->get_errors_tab();
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        $this->inside = Presenter::forge($template_path);
        
        if (isset($edit_id) && $edit_id > 0) {
            $this->edit_id = $edit_id;
            $edit_data = Model_Whitelabel_Payment_Method_Currency::find_by_pk($edit_id);
            
            if ($edit_data !== null) {
                $this->edit_data = $edit_data;
                
                $this->inside->set("edit_data", $edit_data);
                $this->inside->set("edit_id", $this->edit_id);
            }
        }
        
        $this->prepare_currencies_indexed_by_id($whitelabel_payment_method_id);
        
        $this->show_default_tickbox = false;
        $is_default_checked = '';
        if (isset($this->edit_data) &&
                (int)$this->edit_data->is_default === 0 ||
            !isset($this->edit_data)
        ) {
            $this->show_default_tickbox = true;
            if ((null !== Input::post("input.is_default") &&
                    (int)Input::post("input.is_default") === 1)
            ) {
                $is_default_checked = ' checked="checked"';
            }
        }
        
        $result = $this->main_process($whitelabel_payment_method_id);
        
        if ($result !== 0) {
            $this->inside->set("current_kmethod_idx", $current_whitelabel_payment_method_index);
            $this->inside->set("currencies", $this->currencies_indexed_by_id);

            $this->inside->set("show_default_tickbox", $this->show_default_tickbox);
            $this->inside->set("is_default_checked", $is_default_checked);

            if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
                $this->inside->set("whitelabel", $this->whitelabel);
            }

            $this->get_errors_tab();
        }
        
        return $result;
    }
}
