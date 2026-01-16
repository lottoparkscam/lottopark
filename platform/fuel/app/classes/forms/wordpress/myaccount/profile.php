<?php

use Fuel\Core\Validation;
use Helpers\UserHelper;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Myaccount_Profile extends Forms_Main
{
    use Traits_Checks_Block_IP_White;

    private FileLoggerService $fileLoggerService;

    const RESULT_NO_ERRORS_NOT_REQUIRED_TYPE = 100;
    const RESULT_NO_ERRORS_REQUIRED_TYPE = 200;
    const RESULT_NO_ERRORS_REDIRECT = 300;
    const RESULT_ERRORS_EMAIL_NOT_CHANGED = 400;
    const RESULT_ERRORS_EMAIL_CHANGED = 500;
    const RESULT_ERRORS_IP_NOT_ALLOWED = 600;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var array
     */
    private $countries = [];
    
    /**
     *
     * @var array
     */
    private $timezones = [];
    
    /**
     *
     * @var array
     */
    private $phone_countries = [];
    
    /**
     *
     * @var array
     */
    private $user = [];
    
    /**
     *
     * @var array
     */
    private $auser = [];
    
    /**
     *
     * @var array
     */
    private $messages = [];

    /**
     *
     * @var array
     */
    private $groups = [];
    
    /**
     * @param array $countries
     * @param array $timezones
     * @param array $phone_countries
     * @param array $groups
     * @param array $user
     * @param array $auser
     * @param array $whitelabel
     */
    public function __construct(
        $countries,
        $timezones,
        $phone_countries,
        $groups,
        &$user,
        &$auser,
        $whitelabel = []
    ) {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        try {
            if (empty($countries)) {
                throw new Exception("There is empty countries array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        try {
            if (empty($timezones)) {
                throw new Exception("There is empty timezones array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        try {
            if (empty($phone_countries)) {
                throw new Exception("There is empty phone_countries array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        try {
            if (empty($user)) {
                throw new Exception("There is empty user array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        try {
            if (empty($auser)) {
                throw new Exception("There is empty auser array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->countries = $countries;
        $this->timezones = $timezones;
        $this->phone_countries = $phone_countries;
        $this->groups = $groups;
        $this->user = $user;
        $this->auser = $auser;
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_errors(): array
    {
        return $this->errors;
    }

    /**
     *
     * @return array
     */
    public function get_countries(): array
    {
        return $this->countries;
    }

    /**
     *
     * @param array $errors
     */
    public function set_errors($errors): void
    {
        $this->errors = $errors;
    }

    /**
     *
     * @param array $countries
     */
    public function set_countries($countries): void
    {
        try {
            if (empty($countries)) {
                throw new Exception("There is empty countries array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }

        $this->countries = $countries;
    }
    
    /**
     *
     * @return array
     */
    public function get_timezones(): array
    {
        return $this->timezones;
    }

    /**
     *
     * @param array $timezones
     */
    public function set_timezones($timezones): void
    {
        try {
            if (empty($timezones)) {
                throw new Exception("There is empty timezones array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->timezones = $timezones;
    }

    /**
     *
     * @return array
     */
    public function get_phone_countries(): array
    {
        return $this->phone_countries;
    }

    /**
     *
     * @param array $phone_countries
     */
    public function set_phone_countries($phone_countries): void
    {
        try {
            if (empty($phone_countries)) {
                throw new Exception("There is empty phone_countries array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->phone_countries = $phone_countries;
    }

    /**
     *
     * @return array
     */
    public function get_groups(): array
    {
        return $this->groups;
    }

    /**
     *
     * @param array $groups
     */
    public function set_groups($groups): void
    {
        try {
            if (empty($groups)) {
                throw new Exception("There is empty groups array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->groups = $groups;
    }


    /**
     *
     * @return array
     */
    public function get_user()
    {
        return $this->user;
    }

    /**
     *
     * @return array
     */
    public function get_auser()
    {
        return $this->auser;
    }

    /**
     *
     * @param array $user
     */
    public function set_user($user): void
    {
        try {
            if (empty($user)) {
                throw new Exception("There is empty user array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->user = $user;
    }

    /**
     *
     * @param array $auser
     */
    public function set_auser($auser): void
    {
        try {
            if (empty($auser)) {
                throw new Exception("There is empty auser array given");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        $this->auser = $auser;
    }

    /**
     *
     * @return array
     */
    public function get_messages(): array
    {
        return $this->messages;
    }

    /**
     *
     * @param array $messages
     */
    public function set_messages($messages): void
    {
        $this->messages = $messages;
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("profile.name", _("First Name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        
        $validation->add("profile.surname", _("Last Name"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        
        $validation->add("profile.city", _('City'))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
        
        $validation->add("profile.zip", _("Postal/ZIP Code"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);
        
        $validation->add("profile.state", _('Region'))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        
        $validation->add("profile.address", _("Address #1"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        
        $validation->add("profile.address_2", _("Address #2"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
        
        $validation->add("profile.phone", _("Phone"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);
        
        $validation->add("profile.country", _("Country"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['alpha']);
        
        $validation->add("profile.prefix", _("Phone"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        
        $validation->add("profile.timezone", _("Time Zone"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);
        
        $validation->add("profile.birthdate_post", _("Birthdate"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric', 'dashes']);

        $validation->add("profile.national_id", _("National ID"))
            ->add_rule("trim")
            ->add_rule("valid_string", ['alpha', "numeric"])
            ->add_rule("max_length", 30);

        $validation->add("profile.gender", _("Gender"))
            ->add_rule("trim")
            ->add_rule('match_collection', Model_Whitelabel_User::get_gender_keys());

        $validation->add("profile.group", _("Group"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric']);

        $validation->add('profile.email', _('Email'))
            ->add_rule('trim')
            ->add_rule('valid_email');

        $validation->add('profile.remail', _('Repeat e-mail'))
            ->add_rule('trim')
            ->add_rule('valid_email');

        $validation->add('profile.current_password', _('Current password'))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule('min_length', 6);

        return $validation;
    }
    
    /**
     *
     * @param type $val
     * @return int
     * @return int In fact function return int value but before every return it sets
     *              errors which could be read outside that function
     */
    private function validate_and_process_full_form($val): int
    {
        $countries = $this->get_countries();
        $timezones = $this->get_timezones();
        $pcountries = $this->get_phone_countries();
        $groups = $this->get_groups();
        $whitelabel = $this->get_whitelabel();
        
        $user = $this->get_user();
        $auser = $this->get_auser();
            
        if (!($val->validated('profile.country') === "" ||
            (isset($countries) &&
                isset($countries[$val->validated('profile.country')])))
        ) {
            $msg_text = _("Wrong country! Please contact us to solve the issue!");
            $errors = ['profile.country' => $msg_text];
            $this->set_errors($errors);
            return 1;
        }

        $new_prize_payout_group_id = null;
        $old_prize_payout_group_id = $auser['prize_payout_whitelabel_user_group_id'];
        $new_user_user_group_model = null;

        if ((int)$whitelabel['user_can_change_group'] === 1) {
            if (!($val->validated('profile.group') === "" ||
            (isset($groups) &&
                isset($groups[$val->validated('profile.group')])))
                && ($auser['prize_payout_whitelabel_user_group_id'] !== $val->validated('profile.group'))
        ) {
                $msg_text = _("Wrong group! Please contact us to solve the issue!");
                $errors = ['profile.group' => $msg_text];
                $this->set_errors($errors);
                return 1;
            }
            if ($val->validated('profile.group') !== "") {
                $new_prize_payout_group_id = $val->validated('profile.group');
                $user_group_object = Model_Whitelabel_User_Whitelabel_User_Group::find_by(['whitelabel_user_id' => $auser['id'], 'whitelabel_user_group_id' => $val->validated('profile.group')]);
                if (empty($user_group_object)) {
                    $new_user_user_group_model = Model_Whitelabel_User_Whitelabel_User_Group::forge();
                }
            }
        }
        
        $check_region = false;
        if ($val->validated('profile.country') !== "" &&
                Lotto_Helper::check_region(
                    $val->validated("profile.state"),
                    $val->validated("profile.country")
                )
        ) {
            $check_region = true;
        }
        
        if (!($val->validated('profile.state') == "" || $check_region)) {
            $msg_text = _("Wrong region! Please contact us to solve the issue!");
            $errors = ['profile.region' => $msg_text];
            $this->set_errors($errors);
            return 1;
        }
        
        list(
            $date_ok,
            $dt
        ) = Helpers_General::validate_birthday($val->validated("profile.birthdate_post"));
        
        if (!$date_ok) {
            $errors = ['profile.birthdate' => _("Wrong birth date!")];
            $this->set_errors($errors);
            return 1;
        }

        if (!(empty($val->validated('profile.timezone')) ||
            (isset($timezones) &&
                isset($timezones[$val->validated('profile.timezone')])))
        ) {
            $msg_text = _("Wrong timezone! Please contact us to solve the issue!");
            $errors = ['profile.timezone' => $msg_text];
            $this->set_errors($errors);
            return 1;
        }

        if (!empty($val->validated('profile.email')) && $val->validated('profile.email') !== $user['email']) {
            if (empty($val->validated('profile.current_password'))) {
                $this->set_errors(['profile.current_password' => _('Wrong current password! Please try again.')]);
                return 1;
            }

            if (!empty($val->validated('profile.current_password'))) {
                $hash = null;
                $dbuser = Model_Whitelabel_User::check_user_credentials($auser['email'], $val->validated('profile.current_password'), $hash);

                if (is_null($dbuser)) {
                    $this->set_errors(['profile.current_password' => _('Wrong current password! Please try again.')]);
                    return 1;
                }
            }
        }

        if (!empty($val->validated('profile.email')) && !empty($val->validated('profile.remail')) && ($val->validated('profile.email') != $val->validated('profile.remail'))) {
            $msg_text = _('E-mail and Repeat e-mail are not equal!');
            $errors = ['profile.email' => $msg_text];
            $this->set_errors($errors);
            return 1;
        }

        if (empty($val->validated('profile.email'))) {
            $msg_text = _('Please fill in all required fields!');
            $errors = ['profile.email' => $msg_text];
            $this->set_errors($errors);
            return 1;
        }

        list(
            $phone,
            $phone_country,
            $phone_validation_errors
        ) = Helpers_General::validate_phonenumber(
            $val->validated('profile.phone'),
            $val->validated("profile.prefix"),
            $pcountries
        );
        
        $errors = $this->get_errors();
        
        if (!empty($phone_validation_errors)) {
            // should be single key
            $key = key($phone_validation_errors);
            $key_modified = "profile." . $key;
            $errors_phone_validation = [
                $key_modified => $phone_validation_errors[$key]
            ];
            $errors_temp = array_merge($errors_phone_validation, $errors);
            $this->set_errors($errors_temp);
            
            $errors = $errors_temp;
        }

        if (count($errors) > 0) {
            return 1;
        }

        $state = '';
        if ($val->validated('profile.state') !== null) {
            $state = $val->validated('profile.state');
        }

        $birthdate = null;
        if (!empty($val->validated('profile.birthdate_post'))) {
            $birthdate = $val->validated('profile.birthdate_post');
        }

        $auser_set = [
            'name' => $val->validated('profile.name'),
            'surname' => $val->validated('profile.surname'),
            'city' => $val->validated('profile.city'),
            'zip' => $val->validated('profile.zip'),
            'state' => $state,
            'phone' => $phone,
            'phone_country' => $phone_country,
            'address_1' => $val->validated('profile.address'),
            'address_2' => $val->validated('profile.address_2'),
            'country' => $val->validated('profile.country'),
            'birthdate' => $birthdate,
            'timezone' => $val->validated('profile.timezone'),
            'gender' => $val->validated('profile.gender'),
            'national_id' => $val->validated('profile.national_id'),
            'last_update' => DB::expr("NOW()"),
            'prize_payout_whitelabel_user_group_id' => $new_prize_payout_group_id
        ];
        
        $auser->set($auser_set);
        $auser->save();
        \Fuel\Core\Event::trigger('user_edit_profile', [
            'whitelabel_id' => $whitelabel['id'],
            'user_id' => $auser['id'],
            'plugin_data' => $auser_set,
        ]);

        $this->set_auser($auser);

        if (isset($new_user_user_group_model)) {
            $user_id = $auser['id'];
            $old_user_user_group_model = Model_Whitelabel_User_Whitelabel_User_Group::find_by([
                'whitelabel_user_id' => $user_id,
                'whitelabel_user_group_id' => $old_prize_payout_group_id
            ]);
            if (isset($old_user_user_group_model[0])) {
                $old_user_user_group_model[0]->delete();
            }
            $new_user_user_group_model->set([
                'whitelabel_user_id' => $user_id,
                'whitelabel_user_group_id' => $new_prize_payout_group_id
            ]);
            $new_user_user_group_model->save();
        }

        // only if name has changed
        $user_name = trim($user['name'] . ' ' . $user['surname']);
        $user_full_name = $val->validated("profile.name") .
            ' ' . $val->validated("profile.surname");
        $username = trim($user_full_name);

        if ($user_name != $username) {
            $helper = new Helpers_Whitelabel(
                $whitelabel,
                $user,
                $username,
                $user['email']
            );
            $result = $helper->process_cc_method_myaccount_profile();
            
            if (!$result) {
                return 1;
            }
        }

        $messages = $this->get_messages();
        $message = [
            "success",
            _("Your data has been saved!")
        ];
        $messages_temp = array_merge($messages, $message);
        $this->set_messages($messages_temp);
        
        return 0;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $errors = $this->get_errors();
        if (Input::post("profile") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        if (!\Security::check_token()) {
            $msg_text = _('Security error! Please try again.');
            $errors = ['profile' => $msg_text];
            $this->set_errors($errors);
            
            return self::RESULT_SECURITY_ERROR;
        }
        
        $messages = $this->get_messages();
        
        $validated_form = $this->validate_form();
        
        $auser = $this->get_auser();
        $user = $this->get_user();
        $whitelabel = $this->get_whitelabel();

        if ($user && ($this->is_ip_allowed($user['id']) === false)) {
            $msg_text = _('You cannot edit your account.');
            Session::set("message", ["error", $msg_text]);
            
            return self::RESULT_ERRORS_IP_NOT_ALLOWED;
        }

        $isPasswordChangeRequest = !empty(Input::post("profile.password"));
        if ($validated_form->run()) {
            if ($isPasswordChangeRequest) {
                $passwordchange = new Forms_Wordpress_Myaccount_Passwordchange($auser);
                $passwordchange->process_form();
                
                $messages2 = $passwordchange->get_messages();
                if (!empty($messages2)) {
                    $messages = $this->get_messages();
                    $messages_temp = array_merge($messages, $messages2);
                    $this->set_messages($messages_temp);
                }
                
                $errors2 = $passwordchange->get_errors();
                if (!empty($errors2)) {
                    $errors = $this->get_errors();
                    $errors_temp = array_merge($errors, $errors2);
                    $this->set_errors($errors_temp);
                }
            }
            
            $chemail = false;
            $inemail = false;

            $isValidEmailChangeRequest = !empty(Input::post('profile.email')) &&
            !empty(Input::post('profile.remail')) &&
            (Input::post('profile.email') == Input::post('profile.remail')) &&
            Input::post("profile.email") != $user['email'];

            if ($isValidEmailChangeRequest && !$auser->isSocialConnected($user['id'])) {
                $change_email = new Forms_Wordpress_Myaccount_Emailchange(
                    $user,
                    $auser,
                    $whitelabel
                );
                $change_email->process_form();
                $chemail = $change_email->get_chemail();
                $inemail = $change_email->get_inemail();

                $messages2 = $change_email->get_messages();
                if (!empty($messages2)) {
                    $messages = $this->get_messages();
                    $messages_temp = array_merge($messages, $messages2);
                    $this->set_messages($messages_temp);
                }

                $errors2 = $change_email->get_errors();
                if (!empty($errors2)) {
                    $errors = $this->get_errors();
                    $errors_temp = array_merge($errors, $errors2);
                    $this->set_errors($errors_temp);
                } else {
                    \Fuel\Core\Event::trigger('user_edit_email', [
                        'whitelabel_id' => $whitelabel['id'],
                        'user_id' => $auser['id'],
                        'plugin_data' => ["email" => $auser['email']],
                    ]);
                };
            }
            
            $status_validation = $this->validate_and_process_full_form($validated_form);
            
            $errors = $this->get_errors();
            $messages = $this->get_messages();

            if (count($errors) == 0) {
                if ($chemail) {
                    if ((int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                        $message = ["success", _("Your e-mail has been changed.")];
                        $messages_temp = array_merge($messages, $message);
                        $this->set_messages($messages_temp);
                        Session::set("message", $messages);

                        return self::RESULT_NO_ERRORS_NOT_REQUIRED_TYPE;
                    }

                    if ((int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                        $messages = $this->get_messages();
                        Session::set("message", $messages);

                        return self::RESULT_NO_ERRORS_REQUIRED_TYPE;
                    }
                }
                
                Session::set("message", $messages);

                $hasChangedSensitiveData = $isPasswordChangeRequest || $isValidEmailChangeRequest;

                if ($hasChangedSensitiveData) {
                    UserHelper::logOutUser();
                }

                return self::RESULT_NO_ERRORS_REDIRECT;
            } else {
                if ($inemail && !$chemail) {
                    Session::set("message", $messages);
                    
                    return self::RESULT_ERRORS_EMAIL_NOT_CHANGED;
                } elseif ($inemail) {
                    Session::set("message", $messages);
                    
                    return self::RESULT_ERRORS_EMAIL_CHANGED;
                } else {
                    return self::RESULT_WITH_ERRORS;
                }
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->set_errors($errors);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
