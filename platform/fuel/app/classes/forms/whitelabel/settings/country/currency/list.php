<?php

/**
 * Class for preparing Forms_Whitelabel_Settings_Country_Currency_List for Whitelabel
 *
 */
class Forms_Whitelabel_Settings_Country_Currency_List
{
    /**
     *
     * @var bool
     */
    private $full_form = true;
    
    /**
     *
     * @var View
     */
    private $inside;
    
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
     * @var array
     */
    private $available_countries;
    
    /**
     *
     * @var array
     */
    private $all_countries;
    
    /**
     *
     * @var array
     */
    private $default_system_currency = [];
    
    /**
     *
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param string $path_to_view
     * @param string $redirect_path
     */
    public function __construct(
        $whitelabel,
        $should_check_whitelabel,
        $path_to_view,
        $redirect_path
    ) {
        $this->inside = Presenter::forge($path_to_view);
        $this->whitelabel = $whitelabel;
        $this->redirect_path = $redirect_path;
        
        $this->inside->set("whitelabel", $this->whitelabel);
        
        $this->all_countries = Lotto_Helper::get_localized_country_list();
        $whitelabel_country_currencies = Model_Whitelabel_Country_Currency::get_all_by_whitelabel($this->whitelabel);

        // Country which has assigned default value should be removed from the
        // list of available countries
        $countries_with_defaults_temp = [];
        $countries_with_defaults = [];
        foreach ($whitelabel_country_currencies as $key => $whitelabel_country) {
            $countries_with_defaults_temp[] = $whitelabel_country['country_code'];
            $countries_with_defaults[] = [
                'id' => $whitelabel_country['id'],
                'country_code' => $whitelabel_country['country_code'],
                'currency_code' => $whitelabel_country['currency_code'],
            ];
        }

        foreach ($this->all_countries as $key => $country) {
            if (in_array($key, $countries_with_defaults_temp)) {
                $country_wd_key = array_search($key, $countries_with_defaults_temp);
                $countries_with_defaults[$country_wd_key]['country_name'] = $country;
            }
        }

        $this->inside->set("countries_with_defaults", $countries_with_defaults);
        
        if ($should_check_whitelabel) {
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                ;
            } else {
                $this->full_form = false;
            }
        }
    }
    
    /**
     *
     * @return bool
     */
    public function get_full_norm()
    {
        return $this->full_form;
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
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @return string
     */
    public function get_redirect_path()
    {
        return $this->redirect_path;
    }
    
    /**
     *
     * @return array
     */
    public function get_kcurrencies()
    {
        return $this->kcurrencies;
    }
    
    /**
     *
     * @return array
     */
    public function get_vcurrencies()
    {
        return $this->vcurrencies;
    }

    /**
     *
     * @return array
     */
    public function get_available_countries()
    {
        return $this->available_countries;
    }

    /**
     *
     * @return array
     */
    public function get_all_countries()
    {
        return $this->all_countries;
    }

    /**
     *
     * @return array
     */
    public function get_default_system_currency()
    {
        return $this->default_system_currency;
    }
    
    /**
     *
     * @return Validation object
     */
    private function get_prepared_update_form()
    {
        $val = Validation::forge();
        
        $val->add("input.defaultsitecurrency", _("Default site currency"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric");
        
        return $val;
    }
    
    /**
     * @return Validation object
     */
    private function get_prepared_add_new_form()
    {
        $val = Validation::forge();
        
        $val->add("input.sitecurrency", _("Defined currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric");
        
        $val->add("input.first_box_deposit", _("Default deposit for first box"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        $val->add("input.second_box_deposit", _("Default deposit for second box"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        $val->add("input.third_box_deposit", _("Default deposit for third box"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999999999);
        
        return $val;
    }
    
    /**
     *
     * @return Validation object
     */
    private function get_prepared_assign_currency_form()
    {
        $val = Validation::forge();
        
        $val->add("input.country", _("Defined country"))
            ->add_rule('trim')
            ->add_rule('exact_length', 2);
        
        $val->add("input.defaultcurrency", _("Assigned currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric");
        
        return $val;
    }
    
    /**
     *
     * @return null
     */
    public function process_form()
    {
        return ;
    }
}
