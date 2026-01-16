<?php

use Fuel\Core\DB;
use Fuel\Core\Response;
use Fuel\Core\Validation;
use Helpers\Aff\AffHelper;
use Helpers\Wordpress\LanguageHelper;
use Services\Api\Controller;
use Services\Api\Reply;
use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;
use Services\RafflePurchaseService;
use Validators\Rules\Phone;

class Controller_Api_Auth_Register extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"email", "password", "currency"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="currency",
     *                     type="string",
     *                     description="Three letters ISO currency code.",
     *                     example="EUR"
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     type="string",
     *                     description="Two letters ISO language code and two letters ISO country code after dash",
     *                     example="pl_PL"
     *                 ),
     *                 @OA\Property(
     *                     property="group",
     *                     type="int",
     *                     description="ID of group to assing in"
     *                 ),
     *                 @OA\Property(
     *                     property="promo_code",
     *                     type="string",
     *                     description="Experimental - do not use without consultation with team"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="prefix",
     *                     type="string",
     *                     description="Coutry code(ISO 3166) with phone code after underscore.",
     *                     example="PL_48."
     *                 ),
     *                 @OA\Property(
     *                     description="Charity, Company, Legal entity",
     *                     property="company",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          example="success"
     *                      ),
     *                      @OA\Property(
     *                          property="data",
     *                          type="string",
     *                          example="Your account is now fully active."
     *                      )
     *                  )
     *              )
     *          )
     * )
     */

    public function post_index(): Response
    {
        $rafflePurchaseService = Container::get(RafflePurchaseService::class);

        /** @var FileLoggerService $fileLoggerService*/
        $fileLoggerService = Container::get(FileLoggerService::class);

        $validation = Validation::forge('controller_api_auth_register');
        /** This method add custom validation rules. */
        $validation->add_callable('\Validators\CustomRules\IsUniqueInDb');

        $validation->add("currency", "Currency")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 3)
            ->add_rule('valid_string', ['alpha']);

        if ($this->whitelabel->loginForUserIsUsedDuringRegistration()) {
            $validation->add('login', 'Login')
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('required')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes'])
                ->add_rule('isUniqueInDb', ['column' => 'whitelabel_user.login'])
                ->set_error_message('isUniqueInDb', 'Validation rule unique failed for Login');
        }

        $validation->add("email", "Email address")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        $validation->add("password", "Password")
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $validation->add("language", "Language")
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule('min_length', 5)
            ->add_rule('max_length', 5);

        $validation->add("promo_code", "Promo code")
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 50)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("group", "Group")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric']);

        if ($this->whitelabel->isNameSurnameRequiredDuringRegistration()) {
            $validation->add("name", "Name")
                ->add_rule("required")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

            $validation->add("surname", "Surname")
                ->add_rule("required")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        } else {
            $validation->add("name", "Name")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

            $validation->add("surname", "Surname")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        }

        $fieldset = $validation->add('company', 'Charity | Company | Legal Entity')
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        if ($this->whitelabel->isCompanyRequiredDuringRegistration()) {
            $fieldset->add_rule('required');
        }

        if ($this->whitelabel->isPhoneRequiredDuringRegistration()) {
            $validation->add("phone", "Phone")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('min_length', Phone::PHONE_MINIMAL_VALUE_LENGTH)
                ->add_rule('max_length', Phone::PHONE_MAXIMAL_VALUE_LENGTH)
                ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

            $validation->add("prefix", "Phone prefix")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        } else {
            $validation->add("phone", "Phone")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', Phone::PHONE_MINIMAL_VALUE_LENGTH)
                ->add_rule('max_length', Phone::PHONE_MAXIMAL_VALUE_LENGTH)
                ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

            $validation->add("prefix", "Phone prefix")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }

        $is_valid = $validation->run();

        if (!$is_valid) {
            $errors = Lotto_Helper::generate_errors($validation->error());

            return $this->returnResponse(
                $errors,
                Reply::BAD_REQUEST
            );
        }

        // check forbidden emails
        $bademails = file(
            APPPATH . 'vendor/bademails/list.txt',
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );
        $mail_forbidden = false;
        $chkemail = explode("@", $validation->validated("email"));
        $chkdomain = $chkemail[1];
        foreach ($bademails as $bademail) {
            if (substr($chkdomain, -strlen($bademail)) == $bademail) {
                $mail_forbidden = true;
            }
        }

        if ($mail_forbidden) {
            return $this->returnResponse(
                ["This e-mail domain is blocked. Please use another e-mail address."],
                Reply::BAD_REQUEST
            );
        }

        if ($this->whitelabel->assert_unique_emails_for_users) {
            $whitelabel_users = Model_Whitelabel_User::get_count_for_whitelabel_and_email(
                $this->whitelabel->to_array(),
                $validation->validated("email")
            );

            if (is_null($whitelabel_users)) {    // If that situation happen it means that there is a problem with DB
                $fileLoggerService->error(
                    "Variable whitelabel_users is null. Whitelabel.id = {$this->whitelabel->id},
                    email from request {$validation->validated("email")}"
                );

                return $this->returnResponse(
                    ["Unknown error! Please contact us!"],
                    Reply::BAD_REQUEST
                );
            }

            $userscnt = $whitelabel_users[0]['count'];

            if ($userscnt > 0) {
                return $this->returnResponse(
                    ["This e-mail is already registered."],
                    Reply::BAD_REQUEST
                );
            }
        }

        $login = null;
        if ($this->whitelabel->loginForUserIsUsedDuringRegistration()) {
            $login = $validation->validated('login');
        }

        $connected_aff = null;

        if ($this->whitelabel->aff_auto_create_on_register) {
            $affiliateCount = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $this->whitelabel->to_array(),
                $validation->validated("email")
            );

            if (is_null($affiliateCount)) {
                return $this->returnResponse(
                    ["Unknown error! Please contact us!"],
                    Reply::BAD_REQUEST
                );
            }

            $aff_count = $affiliateCount[0]['count'];

            if ($aff_count > 0) {
                return $this->returnResponse(
                    ["This e-mail is already registered."],
                    Reply::BAD_REQUEST
                );
            }
        }

        $currency = Model_Currency::find_one_by(['code' => $validation->validated('currency')]);

        if (!$currency) {
            return $this->returnResponse(
                ["Wrong currency code."],
                Reply::BAD_REQUEST
            );
        }

        $count_country_currencies = Model_Whitelabel_Default_Currency::count_for_whitelabel(
            $this->whitelabel->to_array(),
            $currency->id
        );

        // In that case that currency is equal to 0
        // system will fallback to default currency
        if ($count_country_currencies == 0) {
            $default_system_currency = Helpers_Currency::get_mtab_currency();
            $user_currency_id = intval($default_system_currency['id']);
            $error_text =  "There is no such ID of the currency in DB! ID: " .
                $currency->id;
            $fileLoggerService->error($error_text);
        } else {
            $user_currency_id = intval($currency->id);
        }

        $default_wl_user_group = $this->whitelabel->default_whitelabel_user_group_id;
        $new_user_user_group_model = null;
        if (Input::post('group')) {
            $input_group = $validation->validated('group');
            $group_exists = Model_Whitelabel_User_Group::find_by(['id' => $input_group, 'whitelabel_id' => $this->whitelabel->id]);
            if (empty($group_exists[0])) {
                return $this->returnResponse(
                    ["Wrong group."],
                    Reply::BAD_REQUEST
                );
            }
            $default_wl_user_group = $input_group;
        }
        if (isset($default_wl_user_group)) {
            $new_user_user_group_model = Model_Whitelabel_User_Whitelabel_User_Group::forge();
        }

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($this->whitelabel->to_array());
        $user_language_code = $validation->validated('language');

        if (!$user_language_code) {
            $user_language_code = LanguageHelper::DEFAULT_LANGUAGE_CODE;
        }

        $language = null;

        foreach ($whitelabel_languages as $lang) {
            if ($lang['code'] === $user_language_code) {
                $language = $lang;
            }
        }

        if (!$language) {
            $response = $this->returnResponse(
                ["Invalid language."],
                Reply::BAD_REQUEST
            );

            return $response;
        }

        /** @var Model_Whitelabel_User $newuser */
        $newuser = Model_Whitelabel_User::forge();
        $newsalt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash(
            $validation->validated("password"),
            $newsalt
        );

        $geo_ip = Lotto_Helper::get_geo_IP_record(Lotto_Security::get_IP());
        $country = null;

        if ($geo_ip !== false) {
            $country = $geo_ip->country->isoCode;
        }

        $is_active = 1;
        if ($this->whitelabel->user_activation_type === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
            $is_active = 0;
        }

        $name = '';
        $surname = '';
        if ($this->whitelabel->isNameAndSurnameUsedDuringRegistration()) {
            $name = $validation->validated("name");
            $surname = $validation->validated("surname");
        }

        $company = null;

        if ($this->whitelabel->isCompanyUsedDuringRegistration()) {
            $company = $validation->validated('company');
        }

        $phone = '';
        $phone_country = '';
        if ($this->whitelabel->isPhoneUsedDuringRegistration()) {
            $countries = Lotto_Helper::get_localized_country_list();
            $pcountries = Lotto_Helper::filter_phone_countries($countries);
            list(
                $phone,
                $phone_country,
                $phone_validation_errors
                ) = Helpers_General::validate_phonenumber(
                    $validation->validated('phone'),
                    $validation->validated("prefix"),
                    $pcountries
                );
        }

        // validate phone number
        $errors_phone_validation = [];

        if (!empty($phone_validation_errors)) {
            // should be single key
            $key = key($phone_validation_errors);
            $key_modified = "register." . $key;
            $errors_phone_validation = [
                $key_modified => $phone_validation_errors[$key]
            ];
        }

        if (count($errors_phone_validation) > 0) {
            return $this->returnResponse(
                ["Wrong phone number."],
                Reply::BAD_REQUEST
            );
        }

        $bonus_balance = 0;
        // validate promo code
        if (\Input::post('promo_code')) {
            $promocode_obj = new Forms_Whitelabel_Bonuses_Promocodes_Code(
                $this->whitelabel->to_array(),
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER
            );

            $code = $promocode_obj->check_code($validation->validated('promo_code'));

            if (!$code) {
                return $this->returnResponse(
                    ["Wrong promo code."],
                    Reply::BAD_REQUEST
                );
            }

            if ((int)$code[2]['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY && (float)$code[2]['bonus_balance_amount'] > 0) {
                $amount = (float)$code[2]['bonus_balance_amount'];
                $user_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    $user_currency_id
                );
                $manager_currency_tab = Helpers_Currency::get_mtab_currency();
                $bonus_balance = (float)Helpers_Currency::get_recalculated_to_given_currency(
                    $amount,
                    $manager_currency_tab,
                    $user_currency_tab['code']
                );
            }
        }

        $user_token = Lotto_Security::generate_user_token($this->whitelabel->id);

        if ($this->whitelabel->aff_auto_create_on_register) {
            $parentAffId = null;

            $aff = AffHelper::getAff();
            if (!is_null($aff)) {
                $connectedUser = Model_Whitelabel_User::find_by(['connected_aff_id' => (int)$aff['id']]);
                if (isset($connectedUser)) {
                    $parentAffId = (int)$aff['id'];
                }
            }

            $newaff = Model_Whitelabel_Aff::forge();

            $aff_token = Lotto_Security::generate_aff_token($this->whitelabel->id);
            $subAffToken = Lotto_Security::generate_aff_token($this->whitelabel->id);
            $aff_login = $this->whitelabel['prefix'] . 'U' . $user_token;

            $newaff->set([
                'whitelabel_id' => $this->whitelabel->id,
                'whitelabel_aff_parent_id' => $parentAffId,
                'token' => $aff_token,
                'sub_affiliate_token' => $subAffToken,
                'currency_id' => $this->whitelabel->manager_site_currency_id,
                'language_id' => 1,
                'whitelabel_aff_group_id' => null,
                'is_active' => $is_active,
                'is_confirmed' => 0,
                'is_accepted' => $this->whitelabel->aff_auto_accept,
                'login' => $aff_login,
                'email' => $validation->validated('email'),
                'hash' => $hash,
                'salt' => $newsalt,
                'date_created' => DB::expr('NOW()'),
                'is_deleted' => 0,
                'name' => '',
                'surname' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'country' => '',
                'state' => '',
                'zip' => '',
                'phone_country' => '',
                'birthdate' => null,
                'phone' => '',
                'timezone' => '',
                'company' => $company,
            ]);
            $aff_obj = $newaff->save();
            $connected_aff = $aff_obj[0];
        }

        $refer_user_id = null;

        // create autologin link
        $login_date = new DateTime("now", new DateTimeZone("UTC"));
        $login_hash = Lotto_Security::generate_time_hash($newsalt, $login_date);

        $newuser->set([
            'token' => $user_token,
            'whitelabel_id' => $this->whitelabel->id,
            'language_id' => $language['id'],
            'currency_id' => $user_currency_id,
            'is_active' => $is_active,
            'is_confirmed' => 0,
            'email' => $validation->validated("email"),
            'hash' => $hash,
            'salt' => $newsalt,
            'name' => $name,
            'surname' => $surname,
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'country' => '',
            'state' => '',
            'zip' => '',
            'phone_country' => $phone_country,
            'gender' => Model_Whitelabel_User::GENDER_UNSET,
            'national_id' => '',
            'birthdate' => null,
            'phone' => $phone,
            'timezone' => '',
            'date_register' => DB::expr("NOW()"),
            'balance' => 0,
            'bonus_balance' => $bonus_balance,
            'register_ip' => Lotto_Security::get_IP(),
            'last_ip' => Lotto_Security::get_IP(),
            'last_active' => DB::expr("NOW()"),
            'last_update' => DB::expr("NOW()"),
            'last_country' => $country,
            'register_country' => $country,
            'referrer_id' => $refer_user_id,
            'connected_aff_id' => $connected_aff,
            'login' => $login,
            'prize_payout_whitelabel_user_group_id' => $default_wl_user_group,
            'login_hash' => $login_hash,
            'login_hash_created_at' => $login_date->add(new DateInterval('P1D'))->format('Y-m-d H:i:s'),
            'company' => $company,
        ]);
        $newuser->save();

        if (isset($new_user_user_group_model)) {
            $new_user_user_group_model->set([
                'whitelabel_user_id' => $newuser->id,
                'whitelabel_user_group_id' => $default_wl_user_group
            ]);
            $new_user_user_group_model->save();
        }

        try {
            $rafflePurchaseService->purchase(
                RafflePurchaseService::SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_API,
                $newuser->id
            );
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (isset($code) && isset($code[1])) {
            $code_id = $code[1];
            $promo_code_set = [
                'whitelabel_promo_code_id' => $code_id,
                'whitelabel_user_id' => $newuser->id
            ];
            $promo_code = Model_Whitelabel_User_Promo_Code::forge();
            $promo_code->set($promo_code_set);
            $promo_code->save();

            if (isset($code[2]['whitelabel_aff_id'])) {
                $affiliate = Model_Whitelabel_User_Aff::forge();

                $affiliate->set(["whitelabel_id" => $this->whitelabel->id,
                    "whitelabel_user_id" => $newuser->id,
                    "whitelabel_aff_id" => $code[2]['whitelabel_aff_id'],
                    "whitelabel_aff_medium_id" => null,
                    "whitelabel_aff_campaign_id" => null,
                    "whitelabel_aff_content_id" => null,
                    "is_deleted" => 0,
                    "is_accepted" => 1]);

                $affiliate->save();
            }

            $message = '';
            $send = false;
            if ((int)$code[2]['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT && (float)$code[2]['discount_amount'] > 0) {
                $message = _('You have received a discount to use with your first order!');
                $send = true;
            } elseif ((int)$code[2]['bonus_type'] === Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE) {
                $message = _('You will receive a free ticket with your first order!');
                $send = true;
            } elseif ($bonus_balance > 0) {
                $message = _('You have received bonus money!');
                $send = true;
            }

            if ($send) {
                Model_Whitelabel_User_Popup_Queue::push_message(
                    $this->whitelabel->id,
                    $newuser->id,
                    _('You have received a bonus!'),
                    $message,
                    1
                );
            }
        }

        \Fuel\Core\Event::trigger('user_register', [
            'whitelabel_id' => $this->whitelabel->id,
            'user_id' => $newuser->id,
            'plugin_data' => [
                "token" => Lotto_Helper::get_user_token($newuser, $this->whitelabel->to_array()),
                "created_at" => time(),
                "email" => $newuser->email,
                'currency' => $currency->code ?? "",
                'is_active' => $is_active,
                'is_confirmed' => 0,
                'is_deleted' => 0,
                'name' => $name,
                'surname' => $surname,
                'phone_country' => $phone_country,
                'phone' => $phone,
                'date_register' => time(),
                'balance' => 0,
                'casino_balance' => 0,
                'register_casino' => 0,
                'register_ip' => Lotto_Security::get_IP(),
                'last_ip' => Lotto_Security::get_IP(),
                'last_active' => time(),
                'last_update' => time(),
                'last_country' => $country,
                'register_country' => $country,
                'language' => $language['code'],
                'company' => $company,
            ],
            'register_data' => [
                'event' => 'register',
                'user_id' => $this->whitelabel['prefix'] . 'U' . $newuser->token,
            ]
        ]);

//            Lotto_Helper::hook_with_globals("register", array(
//                "hnewuser" => $newuser,
//                "hrefReader" => $ref_reader,
//            ));

        // send activation email
        if (
            $this->whitelabel->user_activation_type === Helpers_General::ACTIVATION_TYPE_REQUIRED ||
            $this->whitelabel->user_activation_type === Helpers_General::ACTIVATION_TYPE_OPTIONAL
        ) {
            $tdate = new DateTime("now", new DateTimeZone("UTC"));
            $thash = Lotto_Security::generate_time_hash($newsalt, $tdate);

            $newuser->set([
                "activation_hash" => $thash,
                "activation_valid" => $tdate->add(new DateInterval("P1D"))->format("Y-m-d H:i:s")]);
            $newuser->save();

            \Package::load('email');
            $email = Email::forge();
            $email->from('noreply+' . time() . '@' . Lotto_Helper::getWhitelabelDomainFromUrl(), $this->whitelabel->name);

            $email->to($newuser->email);

            // send e-mail
            $domain = $this->whitelabel->domain;
            $home_url = 'https://' . $domain;

            $link = $home_url . '/activation/' . $newuser->token . '/' . $thash;

            $email_data = [
                'link' => $link
            ];

            $create_email = new Forms_Wordpress_Email($this->whitelabel->to_array());
            $email_template = $create_email->get_email('register', $language['code'], $email_data);

            $email->subject($email_template['title']);
            $email->html_body($email_template['body_html']);
            $email->alt_body($email_template['alt_body']);

            try {
                $email->send();
                return $this->returnResponse(
                    [
                        'message' => "Account created! To fully activate your account and get access to all functionalities, please confirm your e-mail address by following the confirmation link we have sent you to your e-mail.",
                        'autologin_url' => 'https://' . $domain . '/autologin/' . $login_hash
                    ]
                );
            } catch (Exception $e) {
                $rhash = Lotto_Security::generate_time_hash($newsalt, $tdate);
                $newuser->set(['resend_hash' => $rhash]);
                $newuser->save();
                $rlink = $home_url . '/resend/' . $newuser->token . '/' . $rhash;

                return $this->returnResponse(
                    [sprintf('Your account is created, however we\'ve encountered ' .
                    'problems while sending the activation e-mail. ' .
                    'Please <a href="%s">try to resend</a> the activation ' .
                    'e-mail or contact us for manual activation.', $rlink)],
                    Reply::BAD_REQUEST
                );
            }
        }

        $login_date = new DateTime("now", new DateTimeZone("UTC"));
        $login_hash = Lotto_Security::generate_time_hash($newsalt, $login_date);

        // create autologin hash
        $newuser->set([
            'login_hash' => $login_hash,
            'login_hash_created_at' => $login_date->add(new DateInterval("P1D"))->format("Y-m-d H:i:s")
        ]);
        $newuser->save();

        // account created without activation
        if ($this->whitelabel->user_activation_type === Helpers_General::ACTIVATION_TYPE_NONE) {
            return $this->returnResponse(
                ["Your account is now fully active."],
                Reply::OK
            );
        }

        return $this->returnResponse(
            ["Your account has been created."],
            Reply::OK
        );
    }
}
