<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Account_Edit form
 */
class Forms_Whitelabel_Account_Edit extends Forms_Main
{
    /**
     *
     * @var bool
     */
    private $full_form = true;
    
    /**
     *
     * @var array
     */
    private $whitelabel;
    
    /**
     *
     * @var array
     */
    private $whitelabel_languages;
    
    /**
     *
     * @var View
     */
    private $inside;
    
    /**
     *
     * @var array
     */
    private $timezones;
    
    /**
     *
     * @var null|array
     */
    private $currencies_indexed_by_id = null;
    
    /**
     *
     * @var bool
     */
    private $edit_manager_currency = false;

    /**
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param bool $edit_manager_currency
     */
    public function __construct(
        $whitelabel,
        $should_check_whitelabel = true,
        $edit_manager_currency = false
    ) {
        $this->whitelabel = $whitelabel;
        $this->edit_manager_currency = $edit_manager_currency;
        
        $this->inside = View::forge("whitelabel/settings/account_edit");
        
        $this->inside->set("whitelabel", $this->whitelabel);
        
        $this->whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel);
        $this->inside->set("languages", $this->whitelabel_languages);
        
        $this->timezones = Lotto_Helper::get_timezone_list();
        $this->inside->set("timezones", $this->timezones);
        
        if ($should_check_whitelabel) {
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                ;
            } else {
                $this->full_form = false;
            }
        }
        
        if ($this->edit_manager_currency) {
            $currencies = Helpers_Currency::getCurrencies();
            $currencies_indexed_by_id = [];
            foreach ($currencies as $currency) {
                $currencies_indexed_by_id[$currency['id']] = $currency['code'];
            }
            asort($currencies_indexed_by_id);

            $this->currencies_indexed_by_id = $currencies_indexed_by_id;

            $this->inside->set("currencies", $this->currencies_indexed_by_id);
        }
        
        $this->inside->set("edit_manager_currency", $this->edit_manager_currency);
    }
    
    /**
     *
     * @return bool
     */
    public function get_full_norm(): bool
    {
        return $this->full_form;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel_languages(): array
    {
        return $this->whitelabel_languages;
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
    public function get_timezones()
    {
        return $this->timezones;
    }

    /**
     *
     * @return null|array
     */
    public function get_currencies_indexed_by_id():? array
    {
        return $this->currencies_indexed_by_id;
    }
        
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
                
        $validation->add("input.name", _("Username"))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 50)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("input.realname", _("Name"))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validation->add("input.email", _("E-mail"))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_email');

        $validation->add("input.language", _("Language"))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('is_numeric')
            ->add_rule("numeric_min", 1);

        $validation->add("input.timezone", _("Time Zone"))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);
        
        if ($this->get_full_norm()) {
            $validation->add("input.maxorderitems", _("Maximum order items"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 1)
                ->add_rule("numeric_max", 9999999);
        }
        
        if ($this->edit_manager_currency) {
            $validation->add("input.managercurrency", _("Manager site currency"))
                ->add_rule("trim")
                ->add_rule("is_numeric");
        }
        
        return $validation;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        if (Input::post("input.name") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        $whitelabel_languages = $this->get_whitelabel_languages();
        $timezones = $this->get_timezones();
        $whitelabel = $this->get_whitelabel();
        $currencies_indexed_by_id = $this->get_currencies_indexed_by_id();
        
        $validated_form = $this->validate_form();
        
        if ($validated_form->run()) {
            $manager_currency_id = null;
            $found = false;
            
            foreach ($whitelabel_languages as $whitelabel_language) {
                if ($whitelabel_language['id'] == $validated_form->validated('input.language')) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $errors = ['input.language' => _("Wrong language!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            if (!isset($timezones[$validated_form->validated('input.timezone')])) {
                $errors = ['input.timezone' => _("Wrong timezone!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            $set_tab = [
                'timezone' => $validated_form->validated("input.timezone"),
                'username' => $validated_form->validated("input.name"),
                'realname' => $validated_form->validated("input.realname"),
                "email" => $validated_form->validated("input.email"),
                'language_id' => $validated_form->validated("input.language")
            ];
            
            if ($this->get_full_norm()) {
                $set_tab["max_order_count"] = $validated_form->validated("input.maxorderitems");
            }
            
            if ($this->edit_manager_currency) {
                if (!empty($validated_form->validated("input.managercurrency")) &&
                    !in_array($validated_form->validated("input.managercurrency"), array_keys($currencies_indexed_by_id))
                ) {
                    $errors = ["input.managercurrency" => _("Incorrect currency.")];
                    $this->inside->set("errors", $errors);
                    return self::RESULT_WITH_ERRORS;
                }

                if (!empty($validated_form->validated("input.managercurrency"))) {
                    $manager_currency_id = $validated_form->validated("input.managercurrency");
                }
                
                $set_tab['manager_site_currency_id'] = $manager_currency_id;
            }
            
            $db_whitelabel = Model_Whitelabel::find_by_pk($whitelabel['id']);
            $db_whitelabel->set($set_tab);
            $db_whitelabel->save();
            
            $cache_domain_value = str_replace('.', '-', $whitelabel['domain']);
            Lotto_Helper::clear_cache(["model_whitelabel.bydomain." . $cache_domain_value]);

            Session::set("whitelabel.name", $validated_form->validated("input.name"));
            Session::set_flash("message", ["success", _("Settings have been saved!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
