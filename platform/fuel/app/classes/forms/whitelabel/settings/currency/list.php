<?php

/**
 * Class for preparing Currency settings for Whitelabel
 *
 */
class Forms_Whitelabel_Settings_Currency_List
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
     * @param int $source
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param string $path_to_view
     * @param string $redirect_path
     */
    public function __construct(
        $source,
        $whitelabel,
        $should_check_whitelabel,
        $path_to_view,
        $redirect_path
    ) {
        $this->source = $source;
        
        $this->inside = Presenter::forge($path_to_view);
        $this->whitelabel = $whitelabel;
        $this->redirect_path = $redirect_path;
        
        $this->inside->set("whitelabel", $this->whitelabel);
        
        $whitelabel_default_currencies = Model_Whitelabel_Default_Currency::get_all_by_whitelabel($this->whitelabel);
        $this->inside->set("available_currencies", $whitelabel_default_currencies);
        
        $show_add_button = false;
        $show_delete_button = false;
        if ($source == Helpers_General::SOURCE_ADMIN) {
            $this->full_form = true;
            $show_add_button = true;
            $show_delete_button = true;
        } elseif ($should_check_whitelabel) {
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                $this->full_form = true;
                $show_add_button = true;
            } else {
                $this->full_form = false;
            }
        }
        
        $this->inside->set("full_form", $this->full_form);
        $this->inside->set("show_add_button", $show_add_button);
        $this->inside->set("show_delete_button", $show_delete_button);
    }
    
    /**
     *
     * @return bool
     */
    public function get_full_form()
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
     * @return void
     */
    public function process_form(): void
    {
        $whitelabel = $this->get_whitelabel();
        $redirect_path = $this->get_redirect_path();
    }
}
