<?php

use Fuel\Core\Cache;
use Fuel\Core\Validation;
use Services\CacheService;

/**
 * Class for preparing Settings form
 */
class Forms_Whitelabel_Settings extends Forms_Main
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
     * @param array $whitelabel
     * @param bool $should_check_whitelabel
     * @param string $path_to_view
     */
    public function __construct($whitelabel, $should_check_whitelabel, $path_to_view)
    {
        $this->inside = Presenter::forge($path_to_view);
        $this->whitelabel = $whitelabel;
        
        $this->inside->set("whitelabel", $this->whitelabel);
        
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel);
        $this->inside->set("langs", $whitelabel_languages);

        if ($should_check_whitelabel) {
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                ;
            } else {
                $this->full_form = false;
            }
        }
        
        $this->inside->set("full_form", $this->full_form);
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();

        $val->add("input.type", _("Type"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 2);

        if ($this->get_full_norm()) {
            $val->add("input.maxpayout", _("Maximum Auto-Payout"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999);
        }

        $val->add("input.register_name_surname", _("Show name and surname fields in registration form"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 2);

        $val->add("input.register_phone", _("Show phone field in registration form"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 2);

        $val->add("input.use_register_company", _("Show company field in registration form"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 2);

        $val->add("input.welcome_popup_timeout", _("First visit welcome popup timeout"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 300);
        
        return $val;
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        if (null === Input::post("input.type")) {
            return self::RESULT_GO_FURTHER;
        }

        $whitelabel = $this->get_whitelabel();
        
        $val = $this->validate_form();

        if ($val->run()) {
            $set = [
                "user_activation_type" => $val->validated("input.type"),
                "register_name_surname" => $val->validated("input.register_name_surname"),
                "register_phone" => $val->validated("input.register_phone"),
                "use_register_company" => $val->validated("input.use_register_company"),
                "welcome_popup_timeout" => $val->validated("input.welcome_popup_timeout"),
                'show_ok_in_welcome_popup' => Input::post('input.show_ok_in_welcome_popup') == 1
            ];
            
            if ($this->get_full_norm()) {
                $set['max_payout'] = $val->validated("input.maxpayout");
            }

            $awl = Model_Whitelabel::find_by_pk($whitelabel['id']);
            $awl->set($set);
            $awl->save();

            /** @var CacheService $cacheService */
            $cacheService = Container::get(CacheService::class);
            /**
             * When cached whitelabel is not removed,
             * signup page does not see changed form
             */
            $cacheService->deleteForWhitelabelByDomain('whitelabel');
            Lotto_Helper::clear_cache(
                [
                    "model_whitelabel.bydomain." . str_replace('.', '-', $whitelabel['domain']),
                    "model_whitelabel_language.whitelabellanguages." . $whitelabel['id']
                ]
            );

            Session::set_flash("message", ["success", _("Settings have been saved!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
