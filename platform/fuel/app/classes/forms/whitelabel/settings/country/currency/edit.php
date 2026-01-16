<?php
use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Settings_Country_Currency_Edit
 */
class Forms_Whitelabel_Settings_Country_Currency_Edit extends Forms_Main
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
     *
     * @var bool
     */
    private $full_form = true;
    
    /**
     *
     * @var array
     */
    private $all_countries;
    
    /**
     *
     * @var int
     */
    private $edit_id = null;
    
    /**
     *
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param string $path_to_view
     * @param string $redirect_path
     * @param int|null $edit_id
     */
    public function __construct(
        $whitelabel,
        $should_check_whitelabel,
        $path_to_view,
        $redirect_path,
        $edit_id = null
    ) {
        $this->inside = Presenter::forge($path_to_view);
        $this->whitelabel = $whitelabel;
        $this->redirect_path = $redirect_path;
        
        $this->all_countries = Lotto_Helper::get_localized_country_list();
        
        if (isset($edit_id) && $edit_id > 0) {
            $this->edit_id = $edit_id;
            $result_edit = Model_Whitelabel_Country_Currency::find_by_pk($edit_id);
            if ($result_edit !== null) {
                $this->inside->set("edit_data", $result_edit);
            }
        }
        
        $whitelabel_country_currencies = Model_Whitelabel_Country_Currency::get_all_by_whitelabel($this->whitelabel);
        
        // Country which has assigned default value should be removed from the
        // list of available countries
        $countries_with_defaults_temp = [];
        foreach ($whitelabel_country_currencies as $key => $whitelabel_country) {
            $countries_with_defaults_temp[] = $whitelabel_country['country_code'];
        }
        
        $available_countries = [];
        foreach ($this->all_countries as $key => $country) {
            if (!in_array($key, $countries_with_defaults_temp) ||
                (isset($edit_id) && $edit_id > 0)
            ) {
                $available_countries[$key] = $country;
            }
        }
        
        $this->inside->set("available_countries", $available_countries);
        
        $whitelabel_default_currencies = Model_Whitelabel_Default_Currency::get_all_by_whitelabel($this->whitelabel);
        $this->inside->set("available_currencies", $whitelabel_default_currencies);
        
        $this->inside->set("whitelabel", $this->whitelabel);
        
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
    public function get_all_countries(): array
    {
        return $this->all_countries;
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        
        $val->add("input.country", _("Defined country"))
            ->add_rule('trim')
            ->add_rule("required")
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
        $whitelabel = $this->get_whitelabel();
        $redirect_path = $this->get_redirect_path();
        
        $val = $this->validate_form();
        
        if ($val->run()) {
            $countries = $this->get_all_countries();
            $country_code = $val->validated("input.country");

            if (!empty($country_code) &&
                !in_array($country_code, array_keys($countries))
            ) {
                $errors = ["input.country" => _("Incorrect country.")];
                $this->inside->set("errors", $errors);
                return;
            }

            $whitelabel_default_currency_id = $val->validated("input.defaultcurrency");

            $default_currency_id = null;
            if (isset($this->edit_id) && $this->edit_id > 0) {
                $default_currency_id = $whitelabel_default_currency_id;
            }
            
            $count_country_currency = Model_Whitelabel_Country_Currency::count_for_whitelabel_by_countrycode(
                $whitelabel,
                $country_code,
                $default_currency_id
            );

            if ($count_country_currency > 0) {
                Session::set_flash("message", ["danger", _("Currency record is already assigned to that country and exist in DB!")]);
                Response::redirect($redirect_path);
            }

            $set = [
                'country_code' => $country_code,
                'whitelabel_default_currency_id' => $whitelabel_default_currency_id
            ];
            
            $whitelabel_country_currency = null;
            if (isset($this->edit_id) && $this->edit_id > 0) {
                $whitelabel_country_currency = Model_Whitelabel_Country_Currency::find_by_pk($this->edit_id);
            } else {
                $whitelabel_country_currency = Model_Whitelabel_Country_Currency::forge();
                $set['whitelabel_id'] = $whitelabel['id'];
            }
            
            $whitelabel_country_currency->set($set);
            $whitelabel_country_currency->save();

            Session::set_flash("message", ["success", _("Assignment currency to country successfully done!")]);
            Response::redirect($redirect_path);
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
        }
        
        return ;
    }
}
