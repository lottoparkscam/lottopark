<?php

use Fuel\Core\Cookie;
use Fuel\Core\Session;
use Fuel\Core\Validation;
use Helpers\Aff\AffHelper;
use Helpers\FlashMessageHelper;
use Helpers\PasswordHelper;
use Helpers\RouteHelper;
use Helpers\SocialMediaConnect\ConnectHelper;
use Helpers\SocialMediaConnect\ProfileHelper;
use Helpers\UserHelper;
use Services\{AffUserService,
    Auth\AbstractAuthService,
    Auth\WordpressLoginService,
    CartService,
    RafflePurchaseService,
    Logs\FileLoggerService,
    SocialMediaConnect\ConnectService,
    SocialMediaConnect\FormService};
use Models\Whitelabel;
use Models\WhitelabelOAuthClient;
use Models\WhitelabelUser;
use Forms\Wordpress\Forms_Wordpress_Email;
use Interfaces\PromoCode\PromoCodeApplicableInterface;
use Validators\Rules\Phone;
use Helpers\Wordpress\LanguageHelper;

/** @deprecated */
class Forms_Wordpress_User_Register extends Forms_Main implements PromoCodeApplicableInterface
{
    private RafflePurchaseService $rafflePurchaseService;
    private FileLoggerService $fileLoggerService;
    private CartService $cartService;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var bool
     */
    private $is_user = false;

    /**
     *
     * @var array
     */
    private $errors = [];

    private ?Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm;

    public function __construct()
    {
        $this->rafflePurchaseService = Container::get(RafflePurchaseService::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->cartService = Container::get(CartService::class);
        $this->errors = Lotto_Settings::getInstance()->get("login_errors");
        if ($this->errors === null) {
            $this->errors = [];
        }
        $this->is_user = Lotto_Settings::getInstance()->get("is_user");
        $this->whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    }

    public function setPromoCodeForm(Forms_Whitelabel_Bonuses_Promocodes_Code $promoCodeForm): void
    {
        $this->promoCodeForm = $promoCodeForm;
    }

    public function getPromoCodeForm(): ?Forms_Whitelabel_Bonuses_Promocodes_Code
    {
        return $this->promoCodeForm;
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
     * @return bool
     */
    public function get_is_user()
    {
        return $this->is_user;
    }

    /**
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     *
     * @param array $errors
     */
    public function set_errors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        $validation->add_callable($this);
        // Honeypot with fake name currency_a,currency_b,currency_c
        $validation->add("register.currency_a", _("Currency(a)"))
            ->add_rule('trim');
        $validation->add("register.currency_b", _("Currency(b)"))
            ->add_rule('trim');
        $validation->add("register.currency_c", _("Currency(c)"))
            ->add_rule('trim');

        /** @var Whitelabel $whitelabelModel */
        $whitelabelModel = Container::get('whitelabel');
        if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
            $validation->add("register.login", _("Login"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes'])
            ->add_rule('unique', 'whitelabel_user.login');
        }

        $validation->add("register.email", _("E-mail address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        $validation->add("register.password", _("Password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $validation->add("register.rpassword", _("Repeat password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6)
            ->add_rule('match_field', "register.password");

        $validation->add("register.ucurrency", _("Currency"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('valid_string', ['numeric']);

        $validation->add("register.promo_code", _("Promo code"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 50)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("register.group", _("Group"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_string', ['numeric']);

        if ($whitelabelModel->isNameSurnameRequiredDuringRegistration()) {
            $validation->add("register.name", _("Name"))
                ->add_rule("required")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 1)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

            $validation->add("register.surname", _("Surname"))
                ->add_rule("required")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 1)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        } else {
            $validation->add("register.name", _("Name"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 1)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);

            $validation->add("register.surname", _("Surname"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', 1)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        }

        if ($whitelabelModel->isPhoneRequiredDuringRegistration()) {
            $validation->add("register.phone", _("Phone"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('min_length', Phone::PHONE_MINIMAL_VALUE_LENGTH)
                ->add_rule('max_length', Phone::PHONE_MAXIMAL_VALUE_LENGTH)
                ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

            $validation->add("register.prefix", _("Phone"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        } else {
            $validation->add("register.phone", _("Phone"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('min_length', Phone::PHONE_MINIMAL_VALUE_LENGTH)
                ->add_rule('max_length', Phone::PHONE_MAXIMAL_VALUE_LENGTH)
                ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

            $validation->add("register.prefix", _("Phone"))
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }

        $fieldset = $validation->add("register.company", _("Charity | Company | Legal Entity"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
        if ($whitelabelModel->isCompanyRequiredDuringRegistration()) {
            $fieldset->add_rule("required");
        }

        $reg_text = _(
            'I accept the <a href="%s" target="_blank">Terms</a>' .
            'and the <a href="%s" target="_blank">Policy</a>'
        );
        $termsSlug = IS_CASINO ? Lotto_Platform::CASINO_TERMS_SLUG : 'terms';
        $privacySlug = IS_CASINO ? Helper_Route::CASINO_PRIVACY_POLICY : 'privacy';
        $register_text = sprintf(
            $reg_text,
            lotto_platform_get_permalink_by_slug($termsSlug),
            RouteHelper::getPermalinkBySlug($privacySlug, $this->whitelabel['domain'])
        );

        $validation->add("register.accept", $register_text)
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("match_value", 1);

        return $validation;
    }

    public static function honeypot_detected(): bool
    {
        return (Input::post("currency_a") != null ||
            Input::post("currency_b") != null ||
            Input::post("currency_c") != null) ?
            true :
            false;
    }

    public static function _validation_unique($val, $options)
    {
        list($table, $field) = explode('.', $options);

        $result = DB::select(DB::expr("LOWER (\"$field\")"))
        ->where($field, '=', Str::lower($val))
        ->from($table)->execute();

        return ! ($result->count() > 0);
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        if (self::honeypot_detected()) {
            $this->set_errors([
                'register' => _('Security error! Please try again.')
            ]);
            return;
        }

        $whitelabel = $this->get_whitelabel();

        // Property register in POST body exists only on /auth/signup page after sending the form
        if ($this->is_user || Input::post("register") === null) {
            return ;
        }

        if (!\Security::check_token()) {
            $errors = [
                'register' => _('Security error! Please try again.')
            ];
            $this->set_errors($errors);
            return ;
        }

        if (
            !Helpers_General::is_development_env() &&
            !Lotto_Security::check_captcha()
        ) {
            $errors = [
                'register' => _('Wrong captcha.')
            ];
            $this->set_errors($errors);
            return;
        }

        $aff = AffHelper::getAff();
        if (((int)$whitelabel['user_registration_through_ref_only'] === 1) && empty($aff)) {
            $errors = [_("The registration is allowed through referral link only.")];
            $this->set_errors($errors);
            return;
        }

        if (
            Input::post("register.ucurrency") !== null &&
            Input::post("register.ucurrency") == "a"
        ) {
            $errors = [
                'register.ucurrency' => _('Please choose currency for your account.')
            ];
            $this->set_errors($errors);
            return ;
        }

        $isSocialConnection = ConnectHelper::isSocialConnection();
        $parametersToValidate = [];
        try {
            if ($isSocialConnection) {
                $userProfile = ProfileHelper::getSocialProfileFromSession();
                if (!empty($userProfile->email)) {
                    $parametersToValidate['register.email'] = $userProfile->email;
                }
                if (!empty($userProfile->firstName)) {
                    $parametersToValidate['register.name'] = $userProfile->firstName;
                }
                if (!empty($userProfile->lastName)) {
                    $parametersToValidate['register.surname'] = $userProfile->lastName;
                }
                $randomPassword = PasswordHelper::generateRandomPassword();
                $parametersToValidate['register.password'] = $randomPassword;
                $parametersToValidate['register.rpassword'] = $randomPassword;
            }
        } catch (Throwable $exception) {
            $this->fileLoggerService->error($exception->getMessage());
        }

        $validated_form = $this->validate_form();
        if ($validated_form->run($parametersToValidate)) {
            $bademails = file(
                APPPATH . 'vendor/bademails/list.txt',
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
            );
            $mail_forbidden = false;
            $chkemail = explode("@", $validated_form->validated("register.email"));
            $chkdomain = $chkemail[1];
            foreach ($bademails as $bademail) {
                if (substr($chkdomain, -strlen($bademail)) == $bademail) {
                    $mail_forbidden = true;
                }
            }

            if ($mail_forbidden) {
                $errors = [
                    'register.email' => _("Your e-mail domain is blocked. Please use another e-mail address.")
                ];
                $this->set_errors($errors);
                return ;
            }

            if ((int)$whitelabel['assert_unique_emails_for_users'] === 1) {
                $res = Model_Whitelabel_User::get_count_for_whitelabel_and_email(
                    $whitelabel,
                    $validated_form->validated("register.email")
                );
                if (is_null($res)) {    // If that situation happen it means that there is a problem with DB
                    try {
                        $errors = [
                            'register.email' =>
                            _("Unknown error! Please contact us!")
                        ];
                        $this->set_errors($errors);
                        throw new Exception("There is a problem with DB!");
                    } catch (Exception $e) {
                        $this->fileLoggerService->error(
                            $e->getMessage()
                        );
                    }

                    return;
                }

                $userscnt = $res[0]['count'];

                if ($userscnt > 0) {
                    $errors = [
                        'register.email' => _('This e-mail is already registered.')
                    ];
                    $this->set_errors($errors);
                    return ;
                }
            }

            $login = null;

            /** @var Whitelabel $whitelabelModel */
            $whitelabelModel = Container::get('whitelabel');
            if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                $login = $validated_form->validated("register.login");
            }

            if ((int)$whitelabel['aff_auto_create_on_register'] === 1) {
                $affCount = Model_Whitelabel_Aff::get_count_for_whitelabel(
                    $whitelabel,
                    $validated_form->validated("register.email")
                );

                if (is_null($affCount)) {
                    $errors = [
                        'register.email' =>
                        _("Unknown error! Please contact us!")
                    ];
                    $this->set_errors($errors);
                    return;
                }

                $aff_count = $affCount[0]['count'];

                if ($aff_count > 0) {
                    $errors = [
                        'register.email' => _('This e-mail is already registered.')
                    ];
                    $this->set_errors($errors);
                    return;
                }
            }

            $count_country_currencies = Model_Whitelabel_Default_Currency::count_for_whitelabel(
                $whitelabel,
                $validated_form->validated("register.ucurrency")
            );

            $user_currency_id = 0;

            // In that case that currency is equal to 0
            // system will fallback to default currency
            if ($count_country_currencies == 0) {
                $default_system_currency = Helpers_Currency::get_mtab_currency();
                $user_currency_id = intval($default_system_currency['id']);
                $error_text =  "There is no such ID of the currency in DB! ID: " .
                    $validated_form->validated("register.ucurrency");
                $this->fileLoggerService->error($error_text);
            } else {
                $user_currency_id = intval($validated_form->validated("register.ucurrency"));
            }

            $default_wl_user_group = $whitelabel['default_whitelabel_user_group_id'];
            $new_user_user_group_model = null;
            if (Input::post('register.group')) {
                $input_group = $validated_form->validated('register.group');
                $group_exists = Model_Whitelabel_User_Group::find_by(['id' => $input_group, 'whitelabel_id' => $whitelabel['id']]);
                if (empty($group_exists[0])) {
                    $errors = ['register.group' => _("Wrong group!")];
                    $this->set_errors($errors);
                    return;
                }
                $default_wl_user_group = $input_group;
            }
            if (isset($default_wl_user_group)) {
                $new_user_user_group_model = Model_Whitelabel_User_Whitelabel_User_Group::forge();
            }

            $wlang = LanguageHelper::getCurrentWhitelabelLanguage();

            /** @var Model_Whitelabel_User $newuser */
            $newuser = Model_Whitelabel_User::forge();
            $newsalt = Lotto_Security::generate_salt();
            $hash = Lotto_Security::generate_hash(
                $validated_form->validated("register.password"),
                $newsalt
            );

            $geo_ip = Lotto_Helper::get_geo_IP_record(Lotto_Security::get_IP());
            $country = null;

            if ($geo_ip !== false) {
                $country = $geo_ip->country->isoCode;
            }

            $refer_user_id = null;
            $uref = Cookie::get('uref');
            if (!empty($uref)) {
                $refer_token = intval(substr($uref, -9, 9));
                $refer_user = Model_Whitelabel_User::get_existing_user_by_token($refer_token, $whitelabel['id']);

                if (!empty($refer_user)) {
                    Model_Whitelabel_Refer_Statistics::add_register($refer_user['id']);
                    $refer_user_id = $refer_user['id'];
                }
                Cookie::delete('uref');
            }

            $is_active = 1;
            if ((int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                $is_active = 0;
            }

            $user_token = Lotto_Security::generate_user_token($whitelabel['id']);

            $name = '';
            $surname = '';

            if ($whitelabelModel->isNameAndSurnameUsedDuringRegistration()) {
                $name = $validated_form->validated("register.name");
                $surname = $validated_form->validated("register.surname");
            }

            $phone = '';
            $phone_country = '';
            if ($whitelabelModel->isPhoneUsedDuringRegistration()) {
                $countries = Lotto_Helper::get_localized_country_list();
                $pcountries = Lotto_Helper::filter_phone_countries($countries);
                list(
                    $phone,
                    $phone_country,
                    $phone_validation_errors
                ) = Helpers_General::validate_phonenumber(
                    $validated_form->validated('register.phone'),
                    $validated_form->validated("register.prefix"),
                    $pcountries
                );
            }

            $company = '';
            if ($whitelabelModel->isCompanyUsedDuringRegistration()) {
                $company = $validated_form->validated("register.company");
            }

            $errors = $this->get_errors();

            if (!empty($phone_validation_errors)) {
                // should be single key
                $key = key($phone_validation_errors);
                $key_modified = "register." . $key;
                $errors_phone_validation = [
                    $key_modified => $phone_validation_errors[$key]
                ];
                $errors_merged = array_merge($errors_phone_validation, $errors);
                $this->set_errors($errors_merged);

                $errors = $errors_merged;
            }

            if (count($errors) > 0) {
                return ;
            }

            $this->promoCodeForm = new Forms_Whitelabel_Bonuses_Promocodes_Code(
                $this->whitelabel,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER
            );

            $this->processPromoCode();

            $promoErrors = $this->promoCodeForm->get_errors();

            if (!empty($promoErrors)) {
                $this->set_errors($promoErrors);
                return;
            }

            $bonus_balance = $this->promoCodeForm->calcUserBonusBalance($user_currency_id);

            $connectedAffId = null;
            if ((int)$whitelabel['aff_auto_create_on_register'] === 1) {
                $parentAffId = null;

                $aff = AffHelper::getAff();
                if (!is_null($aff)) {
                    $parentAffId = (int)$aff['id'];
                }

                $newaff = Model_Whitelabel_Aff::forge();

                $affToken = Lotto_Security::generate_aff_token($whitelabel['id']);
                $affSubToken = Lotto_Security::generate_aff_token($whitelabel['id']);
                $affLogin = $whitelabel['prefix'] . 'U' . $user_token;

                $newaff->set([
                    'whitelabel_id' => $whitelabel['id'],
                    'whitelabel_aff_parent_id' => $parentAffId,
                    'token' => $affToken,
                    'sub_affiliate_token' => $affSubToken,
                    'currency_id' => $whitelabel['manager_site_currency_id'],
                    'language_id' => 1,
                    'whitelabel_aff_group_id' => null,
                    'is_active' => $is_active,
                    'is_confirmed' => 0,
                    'is_accepted' => $whitelabel['aff_auto_accept'],
                    'login' => $affLogin,
                    'email' => $validated_form->validated('register.email'),
                    'hash' => $hash,
                    'salt' => $newsalt,
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
                    'date_created' => DB::expr("NOW()")
                ]);
                $aff_obj = $newaff->save();
                $connectedAffId = $aff_obj[0];
            }

            $newuser->set([
                'token' => $user_token,
                'whitelabel_id' => $whitelabel['id'],
                'language_id' => $wlang['id'],
                'currency_id' => $user_currency_id,
                'is_active' => $is_active,
                'is_confirmed' => 0,
                'email' => $validated_form->validated('register.email'),
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
                'company' => $company,
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
                'connected_aff_id' => $connectedAffId,
                'login' => $login,
                'prize_payout_whitelabel_user_group_id' => $default_wl_user_group
            ]);
            $newuser->save();

            try {
                if ($whitelabelModel->isActivationForUsersRequired()) {
                    $wordpressLoginService = Container::get(WordpressLoginService::class);
                    $resendLink = $wordpressLoginService->getResendLink($newuser['id']);
                    FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, sprintf(_(AbstractAuthService::MESSAGES['activationLink']), $resendLink));
                }

                if ($isSocialConnection) {
                    /**
                     * A lot of people can create account from single device and other people no needed socialConnection which is that's why we delete social connection session.
                     * When session socialConnection is not removed after first user who registered account by social registration,
                     * Second user creating account without social connection creating connection not to his social.
                     */
                    /** @var ConnectService $connectService */
                    $connectService = Container::get(ConnectService::class);
                    $connectService->createSocialConnection($user_token, $whitelabel['id']);
                }
            } catch (Throwable $exception) {
                $this->fileLoggerService->error($exception->getMessage());
            }


            if (isset($new_user_user_group_model)) {
                $new_user_user_group_model->set([
                    'whitelabel_user_id' => $newuser->id,
                    'whitelabel_user_group_id' => $default_wl_user_group
                ]);
                $new_user_user_group_model->save();
            }

            $currency = Model_Currency::find_by_pk($user_currency_id);

            try {
                $this->rafflePurchaseService->purchase(
                    RafflePurchaseService::SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_WEBSITE,
                    $newuser->id
                );
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }

            try {
                $this->saveUserPromoCode($newuser->id);
            } catch (Exception $exception) {
                $this->fileLoggerService->error(
                    'Cannot save Promo Code for user ID: ' . $newuser->id . ': ' . $exception->getMessage()
                );
            }

            try {
                $affUserService = Container::get(AffUserService::class);
                $affUserService->addCampaign($this->promoCodeForm->getPromoCodeCampaign());
                $affUserService->createUser($newuser->id);
            } catch (Exception $exception) {
                $this->fileLoggerService->error(
                    $exception->getMessage()
                );
            }

            try {
                $this->addBonusTicket($newuser);
                $this->sendPromoCodeNotification($newuser);
            } catch (Exception $exception) {
                $this->fileLoggerService->error(
                    $exception->getMessage()
                );
            }

            \Fuel\Core\Event::trigger('user_register', [
                'whitelabel_id' => $whitelabel['id'],
                'whitelabel_theme' => $whitelabel['theme'],
                'user_id' => $newuser->id,
                'plugin_data' => [
                    "token" => Lotto_Helper::get_user_token($newuser),
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
                    'company' => $company,
                    'date_register' => time(),
                    'balance' => 0,
                    'casino_balance' => 0,
                    'register_casino' => IS_CASINO,
                    'register_ip' => Lotto_Security::get_IP(),
                    'last_ip' => Lotto_Security::get_IP(),
                    'last_active' => time(),
                    'last_update' => time(),
                    'last_country' => $country,
                    'register_country' => $country,
                    'language' => $wlang['code']
                ],
                'register_data' => [
                    'event' => 'register',
                    'user_id' => $whitelabel['prefix'] . 'U' . $newuser->token,
                ]
            ]);

            // include register hook
            Lotto_Helper::hook_with_globals("register", [
                'hnewuser' => $newuser,
                'hrefReader' => $affUserService->getRefReader(),
            ]);

            if ((int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                UserHelper::setUserSession(
                    $newuser->id,
                    $newuser->token,
                    $hash,
                    $validated_form->validated('register.email')
                );

                Lotto_Security::reset_IP();
            }

            $order = Session::get("order");
            if (!empty($order)) {
                $this->cartService->createOrUpdateCart($newuser->id, $order);
            }

            $isNotRequiredType = (int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED;
            if (
                (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED ||
                (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_OPTIONAL
            ) {
                $tdate = new DateTime("now", new DateTimeZone("UTC"));
                $thash = Lotto_Security::generate_time_hash($newsalt, $tdate);

                $newuser->set([
                    "activation_hash" => $thash,
                    "activation_valid" => $tdate->add(new DateInterval("P1D"))->format("Y-m-d H:i:s")]);
                $newuser->save();

                \Package::load('email');
                $email = Email::forge();
                $email->from('noreply+' . time() . '@' . Lotto_Helper::getWhitelabelDomainFromUrl(), $whitelabel['name']);

                $email->to($newuser->email);

                // send e-mail
                $link = lotto_platform_get_permalink_by_slug('activation');
                $link = $link . $newuser->token . '/' . $thash;

                $email_data = [
                    'link' => $link
                ];

                $create_email = new Forms_Wordpress_Email($whitelabel);
                $email_template = $create_email->get_email('register', $wlang['code'], $email_data);

                $email->subject($email_template['title']);
                $email->html_body($email_template['body_html']);
                $email->alt_body($email_template['alt_body']);

                if (IS_CASINO) {
                    $deposit_text = _(
                        "You're only one step from playing for millions. Top up your account now to play our games!"
                    );
                } else {
                    $deposit_text = _(
                        "You're only one step from playing for millions in lotteries from around the world. Top up your account now to buy tickets!"
                    );
                }
                Session::set_flash('message_after_register', $deposit_text);

                try {
                    $email->send();
                    if ((int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_NONE) {
                        if (
                            Container::get('whitelabel')->isTheme(Whitelabel::LOTTOHOY_THEME) ||
                            Container::get('whitelabel')->isTheme(Whitelabel::DOUBLEJACK_THEME)
                        ) {
                            Response::redirect(lotto_platform_get_permalink_by_slug('activation'));
                        } else {
                            $this->whitelabelOAuthClientAutologinRedirect();

                            // We would like to redirect user to order page
                            $isCartNotEmpty = !empty(Session::get("order"));
                            if ($isCartNotEmpty && $isNotRequiredType) {
                                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                            }

                            if (Container::get('whitelabel')->isTheme(Whitelabel::LOTTOPARK_THEME)) {
                                Response::redirect(lotto_platform_get_permalink_by_slug('welcome'));
                            } else {
                                Response::redirect(lotto_platform_get_permalink_by_slug('deposit'));
                            }
                        }
                    }
                } catch (Exception $e) {
                    $rhash = Lotto_Security::generate_time_hash($newsalt, $tdate);
                    $newuser->set(['resend_hash' => $rhash]);
                    $newuser->save();
                    $rlink = lotto_platform_home_url('/');
                    $rlink = $rlink . 'resend/' . $newuser->token . '/' . $rhash;
                    $warning_text = sprintf(
                        _(
                            'Your account is created, however we\'ve encountered ' .
                            'problems while sending the activation e-mail. ' .
                            'Please <a href="%s">try to resend</a> the activation ' .
                            'e-mail or contact us for manual activation.'
                        ),
                        $rlink
                    );

                    Session::set("message", ["warning", $warning_text]);

                    $this->whitelabelOAuthClientAutologinRedirect();

                    // We would like to redirect user to order page
                    $isCartNotEmpty = !empty(Session::get("order"));
                    if ($isCartNotEmpty) {
                        Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                    }

                    if (Container::get('whitelabel')->isTheme(Whitelabel::LOTTOPARK_THEME)) {
                        Response::redirect(lotto_platform_get_permalink_by_slug('welcome'));
                    } else {
                        Response::redirect(lotto_platform_get_permalink_by_slug('deposit'));
                    }
                }
            }

            if ((int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_NONE) {
                if (Container::get('whitelabel')->isTheme(Whitelabel::LOTTOHOY_THEME)) {
                    Response::redirect(lotto_platform_get_permalink_by_slug('activated'));
                } else {
                    $this->whitelabelOAuthClientAutologinRedirect();

                    // We would like to redirect user to order page
                    $isCartNotEmpty = !empty(Session::get("order"));
                    if ($isCartNotEmpty) {
                        Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                    }

                    if (Container::get('whitelabel')->isTheme(Whitelabel::LOTTOPARK_THEME)) {
                        Response::redirect(lotto_platform_get_permalink_by_slug('welcome'));
                    } else {
                        Response::redirect(lotto_platform_get_permalink_by_slug('deposit'));
                    }
                }
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->set_errors($errors);
        }

        return ;
    }

    public function processPromoCode(): void
    {
        if ($this->promoCodeForm) {
            $this->promoCodeForm->process_form();
        }
    }

    /**
     * @throws Exception
     */
    public function saveUserPromoCode(int $userId): void
    {
        if ($this->promoCodeForm) {
            $this->promoCodeForm->saveUserPromoCode($userId);
        }
    }

    /**
     * @throws Exception
     */
    public function addBonusTicket(WhitelabelUser|Model_Whitelabel_User $user): void
    {
        if ($this->promoCodeForm && $this->promoCodeForm->isPromoCodeBonusTypeFreeLine()) {
            if (empty($this->whitelabel)) {
                $this->whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            }

            $promo = $this->promoCodeForm->get_promo_code();

            if (empty($promo['campaign']['lottery_id'])) {
                throw new Exception(sprintf(
                    'Could not get valid \'lottery_id\' for WhitelabelCampaign ID: %s, User ID: %s',
                    $promo['code_id'],
                    $user->id
                ));
            }

            $lotteryId = $promo['campaign']['lottery_id'];
            $lottery = Model_Lottery::get_single_row_by_id($lotteryId);

            $bonusTicket = new Forms_Wordpress_Bonuses_Ticket_Ticket(
                $this->whitelabel,
                $user->to_array(),
                $lottery
            );

            $resultTicket = $bonusTicket->process_form();

            if ($resultTicket !== Forms_Wordpress_Bonuses_Ticket_Ticket::RESULT_OK) {
                $message = 'There is something wrong with DB. ' .
                    'No bonus ticket added for whitelabel ID: ' .
                    $this->whitelabel['id'] .
                    ' and user ID: ' . $user['id'];

                throw new Exception($message);
            }

            $lotteryType = $bonusTicket->get_lottery_type();
            $whitelabelUserTicket = $bonusTicket->get_new_bonus_ticket();

            $newBonusTicketLine = new Forms_Wordpress_Bonuses_Ticket_Line(
                $lottery,
                $lotteryType,
                $whitelabelUserTicket
            );

            $resultTicketLine = $newBonusTicketLine->process_form();

            if ($resultTicketLine !== Forms_Wordpress_Bonuses_Ticket_Line::RESULT_OK) {
                $message = 'There is something wrong with DB. ' .
                    'No bonus ticket line added for whitelabel ID: ' .
                    $this->whitelabel['id'] .
                    ' and user ID: ' . $user['id'];

                throw new Exception($message);
            }

            Lotto_Helper::create_slips_for_ticket($whitelabelUserTicket);

            $notification_draw = new Helpers_Notifications_Draw();
            $notification_draw->new_record([$whitelabelUserTicket]);
        }
    }

    private function sendPromoCodeNotification(Model_Whitelabel_User $user): void
    {
        if (!$this->promoCodeForm || !$this->promoCodeForm->issetPromoCode()) {
            return;
        }

        /** @var string[]|float[] $campaign */
        $campaign = $this->promoCodeForm->getPromoCodeCampaign();
        $isCampaignDiscountGreaterThanZero = $this->promoCodeForm->isPromoCodeBonusTypeDiscount()
            && (float) $campaign['discount_amount'] > 0;

        $message = '';
        $isSend = false;

        if ($isCampaignDiscountGreaterThanZero) {
            $message = _('You have received a discount to use with your first order!');
            $isSend = true;
        } elseif ($this->promoCodeForm->isPromoCodeBonusTypeFreeLine()) {
            $message = _('You have received a free ticket!');
            $isSend = true;
        } elseif ($user->bonus_balance > 0) {
            $message = _('You have received bonus money!');
            $isSend = true;
        }

        if ($isSend) {
            Model_Whitelabel_User_Popup_Queue::push_message(
                $this->whitelabel['id'],
                $user->id,
                _('You have received a bonus!'),
                $message,
                1
            );
        }
    }

    /**
     * Continue autologin flow to the Whitelabel OAuth client site
     */
    private function whitelabelOAuthClientAutologinRedirect(): void
    {
        if (UserHelper::isUserLogged()) {
            $autologinUri = Cookie::get(WhitelabelOAuthClient::AUTOLOGIN_URI_KEY);

            if (!empty($autologinUri)) {
                Response::redirect($autologinUri);
            }
        }
    }
}
