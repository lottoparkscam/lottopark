<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Edit form
 */
class Forms_Whitelabel_Edit extends Forms_Main
{
    use Traits_Gets_States;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var bool
     */
    private $full_form = true;
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @var array
     */
    private $kcurrencies;
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
        
//        if ((int)$this->whitelabel['type'] !== (int)Helpers_General::WHITELABEL_TYPE_V1 ||
//            $this->whitelabel['id'] == Helpers_General::WHITELABEL_ID_SPECIAL
//        ) {
//            ;
//        } else {
//            $this->full_form = false;
//        }
        
        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        foreach ($currencies as $currency) {
            $kcurrencies[$currency['id']] = $currency['code'];
        }
        asort($kcurrencies);

        $this->kcurrencies = $kcurrencies;
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
     * @return bool
     */
    public function get_full_form(): bool
    {
        return $this->full_form;
    }

    /**
     *
     * @return Presenter_Admin_Whitelabels_Edit
     */
    public function get_inside(): Presenter_Admin_Whitelabels_Edit
    {
        return $this->inside;
    }
        
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $validation->add("input.name", _("Name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("min_length", 3)
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ['alpha', 'numeric', 'dashes', 'spaces', 'dots']);

        $validation->add("input.margin", _("Margin"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 100);

        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
            $validation->add("input.prepaid_alert_limit", _("Prepaid alert limit"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);
        }
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
            Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $validation->add("input.maxorderitems", _("Maximum order items"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 1)
                ->add_rule("numeric_max", 9999999);
        }

        $validation->add("input.email", _("E-mail"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_email");

        $validation->add("input.realname", _("Real name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validation->add("input.domain", _("Domain"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_url");

        $validation->add("input.company", _("Company"))
            ->add_rule("trim");

        $validation->add("input.username", _("Username"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("input.password", _("Password"))
            ->add_rule("trim")
            ->add_rule('min_length', 6);
        
        $validation->add("input.managercurrency", _("Manager site currency"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric");

        $validation->add("input.us_state_active", _("Send US state information to L-Tech"))
            ->add_rule("trim");
        
        $validation->add("input.is_report", _("Should be cosidered in reports"))
            ->add_rule("trim");

        $validation->add("input.enabled_us_states", _("Enabled US States"));

        return $validation;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $whitelabel['enabled_us_states'] = (empty($whitelabel['enabled_us_states']) || $whitelabel['enabled_us_states'] == "N;") ? [] : unserialize($whitelabel['enabled_us_states']);
        
        $form_path = "admin/whitelabels/edit";
        
        $this->inside = Presenter::forge($form_path);
        $this->inside->set("whitelabel", $whitelabel);
        $this->inside->set("currencies", $this->kcurrencies);
        $this->inside->set("us_states", $this->get_us_states());
        
        if (Input::post("input") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $whitelabels = Model_Whitelabel::exist_domain_username(
                $whitelabel,
                $validated_form->validated("input.domain"),
                $validated_form->validated("input.username")
            );

            if (is_null($whitelabels)) {
                $errors = ["input.domain" => _("There is something wrong with database!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            if ((int)$whitelabels[0]['count'] !== 0) {
                $errors = ["input.domain" => _("Whitelabel with this domain or username already exists!")];
                $this->inside->set("error_whitelabel_exists", 1);
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            if (!empty($validated_form->validated("input.managercurrency")) &&
                !in_array($validated_form->validated("input.managercurrency"), array_keys($this->kcurrencies))
            ) {
                $errors = ["input.managercurrency" => _("Incorrect currency.")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }
            
            $manager_currency_id = $validated_form->validated("input.managercurrency");

            $us_state_active = ($validated_form->validated("input.us_state_active") == 1) ? 1 : 0;

            $is_report = ($validated_form->validated("input.is_report") == 1) ? 1 : 0;

            $set = [
                "name" => $validated_form->validated("input.name"),
                "email" => $validated_form->validated("input.email"),
                "realname" => $validated_form->validated("input.realname"),
                "margin" => $validated_form->validated("input.margin"),
                "domain" => parse_url($validated_form->validated("input.domain"), PHP_URL_HOST),
                "company_details" => $validated_form->validated("input.company"),
                "username" => $validated_form->validated("input.username"),
                "manager_site_currency_id" => $manager_currency_id,
                "us_state_active" => $us_state_active,
                "enabled_us_states" => serialize($validated_form->validated("input.enabled_us_states")),
                "is_report" => $is_report,
            ];
            
            $whitelabel_new = Model_Whitelabel::find_by_pk($whitelabel['id']);
            $whitelabel_new->set($set);

            if (!empty($validated_form->validated("input.password"))) {
                $salt = Lotto_Security::generate_salt();
                $hash = Lotto_Security::generate_hash($validated_form->validated("input.password"), $salt);
                $whitelabel_new->set([
                    "hash" => $hash,
                    "salt" => $salt
                ]);
            }

            $whitelabel_new_set = [];
            
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
                $whitelabel_new_set["prepaid_alert_limit"] = $validated_form->validated("input.prepaid_alert_limit");
            }
            
            if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
                Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
            ) {
                $whitelabel_new_set["max_order_count"] = $validated_form->validated("input.maxorderitems");
            }
            
            $whitelabel_new->set($whitelabel_new_set);
            
            $whitelabel_new->save();

            Lotto_Helper::clear_cache('model_whitelabel.bydomain');
            Session::set_flash('message', ['success', _("Whitelabel has been saved!")]);
            
            return self::RESULT_OK;
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
        }
        
        return self::RESULT_WITH_ERRORS;
    }
}
