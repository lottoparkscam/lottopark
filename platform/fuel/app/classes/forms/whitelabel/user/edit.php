<?php

use Fuel\Core\Validation;
use Fuel\Core\Presenter;

/**
 *
 */
class Forms_Whitelabel_User_Edit extends Forms_Main
{
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
    
    /**
     * @param array $whitelabel
     */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        
        $val->add("input.name", _("First Name"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        
        $val->add("input.surname", _("Last Name"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        
        $val->add("input.city", _("City"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        
        $val->add("input.zip", _("Postal/ZIP Code"))
            ->add_rule('trim')
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);
        
        $val->add("input.state", _("Region"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        
        $val->add("input.address", _("Address #1"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        
        $val->add("input.address_2", _("Address #2"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        
        $val->add("input.phone", _("Phone"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);
        
        $val->add("input.country", _("Country"))
            ->add_rule('trim')
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['alpha']);
        
        $val->add("input.prefix", _("Phone"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        
        $val->add("input.timezone", _("Time Zone"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);
        
        $val->add("input.birthdate", _("Birthdate"))
            ->add_rule('trim')
            ->add_rule('valid_string', ['numeric', 'forwardslashes']);

        // TODO: {Vordis 2019-05-27 13:51:27} we could and should make generic validator, which would implement user rules for all places (admin, whitelabel, user profile)
        $val->add("input.national_id", _("National ID"))
            ->add_rule("trim")
            ->add_rule("valid_string", ['alpha', "numeric"])
            ->add_rule("max_length", 30);

        $val->add("input.gender", _("Gender"))
            ->add_rule("trim")
            ->add_rule('match_collection', Model_Whitelabel_User::get_gender_keys());
        
        return $val;
    }
    
    /**
     *
     * @param string $view_template
     * @param Model_Whitelabel_User $user
     * @return int
     */
    public function process_form(string $view_template, $user): int
    {
        $this->inside = Presenter::forge($view_template);
        $this->inside->set("user", $user);
        
        $errors = [];
        $whitelabel = $this->get_whitelabel();
        $countries = Lotto_Helper::get_localized_country_list();

        $pcountries = Lotto_Helper::filter_phone_countries($countries);

        $prefixes = Lotto_Helper::get_telephone_prefix_list();

        $this->inside->set("prefixes", $prefixes);
        $this->inside->set("countries", $countries);
        $this->inside->set("pcountries", $pcountries);
        $timezones = Lotto_Helper::get_timezone_list();
        $this->inside->set("timezones", $timezones);

        if (Input::post("input.name") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $val = $this->validate_form();

        if ($val->run()) {
            $check_country = ($val->validated('input.country') === "" ||
                isset($countries[$val->validated('input.country')]));
            if (!$check_country) {
                $errors = ['input.country' => _("Wrong country!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            $check_region = $val->validated('input.state') == "" ||
            (
                $val->validated('input.country') !== "" &&
                Lotto_Helper::check_region(
                    $val->validated("input.state"),
                    $val->validated("input.country")
                )
            );
            if (!$check_region) {
                $errors = ['input.region' => _("Wrong region!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            list(
                $date_ok,
                $dt
            ) = Helpers_General::validate_birthday(
                $val->validated('input.birthdate'),
                "m/d/Y"
            );

            if (!$date_ok) {
                $errors = ['input.birthdate' => _("Wrong birthdate!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            $check_timezone = empty($val->validated('input.timezone')) ||
                isset($timezones[$val->validated('input.timezone')]);
            if (!$check_timezone) {
                $errors = ['input.timezone' => _("Wrong timezone!")];
                $this->inside->set("errors", $errors);
                return self::RESULT_WITH_ERRORS;
            }

            list(
                $phone,
                $phone_country,
                $phone_validation_errors
            ) = Helpers_General::validate_phonenumber(
                $val->validated('input.phone'),
                $val->validated("input.prefix"),
                $pcountries
            );

            if (!empty($phone_validation_errors)) {
                // should be single key
                $key = key($phone_validation_errors);
                $key_modified = "input." . $key;
                $errors = [
                    $key_modified => $phone_validation_errors[$key]
                ];
                $this->inside->set("errors", $errors);
            }

            if (isset($errors) && count($errors) === 0) {
                $state = '';
                if ($val->validated('input.state') !== null) {
                    $state = $val->validated('input.state');
                }
                
                $birthdate = null;
                if ($dt !== null) {
                    $birthdate = $dt->format('Y-m-d');
                }
                
                $user->set([
                    'name' => $val->validated('input.name'),
                    'surname' => $val->validated('input.surname'),
                    'city' => $val->validated('input.city'),
                    'zip' => $val->validated('input.zip'),
                    'state' => $state,
                    'phone' => $phone,
                    'phone_country' => $phone_country,
                    'address_1' => $val->validated('input.address'),
                    'address_2' => $val->validated('input.address_2'),
                    'country' => $val->validated('input.country'),
                    'birthdate' => $birthdate,
                    'timezone' => $val->validated('input.timezone'),
                    'gender' => $val->validated('input.gender'),
                    'national_id' => $val->validated('input.national_id'),
                    'last_update' => DB::expr("NOW()")
                ]);
                $user->save();
                \Fuel\Core\Event::trigger('user_edit_profile', [
                    'whitelabel_id' => $whitelabel['id'],
                    'user_id' => $user->id,
                    'plugin_data' => $user,
                ]);

                $username = trim($val->validated("input.name") . ' ' . $val->validated("input.surname"));

                $helper = new Helpers_Whitelabel(
                    $whitelabel,
                    $user,
                    $username,
                    $user['email']
                );
                
                $edit_ok = $helper->process_cc_method_user_edit();

                if (!$edit_ok) {
                    return self::RESULT_WITH_ERRORS;
                }
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
