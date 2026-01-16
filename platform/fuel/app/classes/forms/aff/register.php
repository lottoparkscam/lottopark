<?php

use Fuel\Core\Validation;
use Models\WhitelabelAff;
use Repositories\Aff\WhitelabelAffRepository;
use Services\AffService;
use Services\Logs\FileLoggerService;

/**
 * @deprecated
 * Description of Forms_Aff_Register
 */
class Forms_Aff_Register extends Forms_Main
{
    const RESULT_EMAIL_NOT_SENT = 100;
    const RESULT_EMPTY_TOKEN = 200;
    const RESULT_EMPTY_HASH = 300;
    const RESULT_WRONG_LINK = 400;
    const RESULT_ACTIVATION = 500;
    const RESULT_ACTIVATION_LINK_EXPIRED = 600;
    const RESULT_ALREADY_ACTIVATED = 700;
    const RESULT_EMAIL_SENT = 800;
    
    const EMAIL_TYPE_REGISTRATION = 1;
    const EMAIL_TYPE_RESEND = 2;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var Presenter_Aff_Register
     */
    private $inside = null;
    
    /**
     *
     * @var string
     */
    private $domain_link = "";
    
    /**
     *
     * @var string
     */
    private $whitelabel_domain = "";

    /**
     *
     * @var DateTime
     */
    private $time_date = null;
    
    /**
     *
     * @var string
     */
    private $salt = null;
    
    /**
     *
     * @var string
     */
    private $activation_link = "";
    
    /**
     *
     * @var string
     */
    private $new_aff_login = "";
    
    /**
     *
     * @var string
     */
    private $new_aff_email = "";
    
    /**
     * Zero means that aff is inactive
     * One means that aff is active
     * @var int
     */
    private $aff_active_status = 0;
    
    /**
     *
     * @var string
     */
    private $new_aff_token = "";

    private AffService $affService;

    private WhitelabelAffRepository $whitelabelAffRepository;
    private FileLoggerService $fileLoggerService;

    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
        $this->whitelabel_domain = $this->whitelabel['domain'];
        $this->domain_link = "https://aff." . $this->whitelabel_domain . "/";
        $this->affService = Container::get(AffService::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @return Presenter_Aff_Register
     */
    public function get_inside(): Presenter_Aff_Register
    {
        return $this->inside;
    }

    /**
     *
     * @return \DateTime
     */
    private function get_time_date(): \DateTime
    {
        if (is_null($this->time_date)) {
            $this->time_date = new DateTime("now", new DateTimeZone("UTC"));
        }
        
        return $this->time_date;
    }

    /**
     *
     * @return string
     */
    public function get_salt(): string
    {
        if (is_null($this->salt)) {
            $this->salt = Lotto_Security::generate_salt();
        }
        
        return $this->salt;
    }

    /**
     *
     * @return string
     */
    public function get_link_to_manager(): string
    {
        $link_to_manager = "https://manager." .
            $this->get_whitelabel_domain() . "/";
        return $link_to_manager;
    }
    
    /**
     *
     * @return string
     */
    public function get_whitelabel_email(): string
    {
        $whitelabel_email = (string)$this->whitelabel['email'];
        
        return $whitelabel_email;
    }
    
    /**
     *
     * @return string
     */
    public function get_whitelabel_name(): string
    {
        $whitelabel_name = (string)$this->whitelabel['name'];
        
        return $whitelabel_name;
    }
    
    /**
     *
     * @return string
     */
    public function get_activation_link(): string
    {
        return $this->activation_link;
    }
    
    /**
     *
     * @return string
     */
    public function get_registration_title(): string
    {
        $registration_title = $this->get_whitelabel_name();
        $registration_title .= ' - ' . _("Account Activation");
        return $registration_title;
    }
    
    /**
     *
     * @return string
     */
    public function get_email_title_to_manager(): string
    {
        $email_title_to_manager = $this->get_whitelabel_name();
        $email_title_to_manager .= " - " . _("New affiliate registration");
        return $email_title_to_manager;
    }
    
    /**
     *
     * @return string
     */
    public function get_from_email(): string
    {
        $domain = $this->get_whitelabel_domain();
        if (empty($domain)) {
            $domain =  Lotto_Helper::getWhitelabelDomainFromUrl();
        }
        $from_email = 'noreply@' . $domain;
        return $from_email;
    }
    
    /**
     *
     * @param bool $as_html
     * @return string
     */
    public function get_content_of_registration_email(bool $as_html = true): string
    {
        $break_tag = '';
        if ($as_html) {
            $break_tag = '<br>';
        }
        
        $content_of_registration_email = _(
            "Thank you for registration! To complete the activation " .
            "process please follow the link: "
        );
        
        if ($as_html) {
            $content_of_registration_email .= _('<a href="%1$s">activation link</a>.');
        } else {
            $content_of_registration_email .= _('%1$s');
        }
        
        return $content_of_registration_email;
    }
    
    /**
     *
     * @param bool $as_html
     * @return string
     */
    public function get_content_of_email_to_manager(bool $as_html = true): string
    {
        $break_tag = '';
        if ($as_html) {
            $break_tag = '<br>';
        }
        
        $content_of_email_to_manager = _("There has been a new registration in ") .
            $this->get_whitelabel_name() .
            _(" affiliate system. ") . $break_tag .
            _("Affiliate login: ") . $this->get_new_aff_login() . $break_tag .
            _("Affiliate e-mail: ") . $this->get_new_aff_email() . $break_tag .
            _("Status: ") . $this->get_active_status_as_text() . $break_tag .
            _("You can view the affiliate details ");
        
        if ($as_html) {
            $content_of_email_to_manager .= _('<a href="%1$s">here</a>.');
        } else {
            $content_of_email_to_manager .= _('here: %1$s');
        }
        
        return $content_of_email_to_manager;
    }
    
    /**
     *
     * @return string
     */
    public function get_new_aff_login(): string
    {
        return $this->new_aff_login;
    }

    /**
     *
     * @return string
     */
    public function get_new_aff_email(): string
    {
        return $this->new_aff_email;
    }

    /**
     *
     * @return string
     */
    public function get_whitelabel_domain(): string
    {
        return $this->whitelabel_domain;
    }
    
    /**
     *
     * @return int
     */
    public function get_active_status(): int
    {
        return $this->aff_active_status;
    }
    
    /**
     *
     * @return string
     */
    public function get_active_status_as_text(): string
    {
        $status_text = "";
        $status = $this->get_active_status();
        
        if ($status === 0) {
            $status_text = _("inactive");
        } else {
            $status_text = _("active");
        }
        
        return $status_text;
    }
    
    /**
     *
     * @return string
     */
    public function get_link_to_manager_list(): string
    {
        $link_to_manager_list = $this->get_link_to_manager();
        
        $status = $this->get_active_status();
        if ($status === 0) {
            $status_text = 'affs/inactive';
        } else {
            $new_aff_token = $this->get_new_aff_token();
            $status_text = 'affs/list/view/' . $new_aff_token;
        }
        $link_to_manager_list .= $status_text;
        
        return $link_to_manager_list;
    }
    
    /**
     *
     * @return string
     */
    public function get_new_aff_token(): string
    {
        return $this->new_aff_token;
    }
    
    /**
     *
     * @param string $filename Could be empty - if it is it will use default
     * @return \Forms_Aff_Register
     */
    public function set_inside_by_presenter(string $filename): Forms_Aff_Register
    {
        $this->inside = Presenter::forge($filename);
        return $this;
    }

    /**
     *
     * @param string $new_aff_login
     * @return \Forms_Aff_Register
     */
    public function set_new_aff_login(string $new_aff_login): Forms_Aff_Register
    {
        $this->new_aff_login = $new_aff_login;
        return $this;
    }

    /**
     *
     * @param string $new_aff_email
     * @return \Forms_Aff_Register
     */
    public function set_new_aff_email(string $new_aff_email): Forms_Aff_Register
    {
        $this->new_aff_email = $new_aff_email;
        return $this;
    }

    /**
     *
     * @param string $activation_link
     * @return \Forms_Aff_Register
     */
    public function set_activation_link(string $activation_link): Forms_Aff_Register
    {
        $this->activation_link = $activation_link;
        return $this;
    }
    
    /**
     *
     * @param string $new_aff_token
     * @return \Forms_Aff_Register
     */
    public function set_new_aff_token(string $new_aff_token): Forms_Aff_Register
    {
        $this->new_aff_token = $new_aff_token;
        return $this;
    }
    
    /**
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("register.email", _("E-mail address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');
        
        $validation->add("register.login", _("Login"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'utf8', 'numeric', 'dashes']);

        $validation->add("register.password", _("Password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6);
        
        $validation->add("register.password_repeat", _("Repeat password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6)
            ->add_rule('match_field', "register.password");

        return $validation;
    }
    
    /**
     *
     * @param WhitelabelAff $aff
     * @param string $resend_message_start
     * @param string $resend_message_end
     * @return void
     */
    public function create_resend_message(
        WhitelabelAff $aff,
        string $resend_message_start,
        string $resend_message_end
    ): void {
        $salt = $this->get_salt();
        $time_date = $this->get_time_date();
        $resend_hash = Lotto_Security::generate_time_hash($salt, $time_date);
                
        $set = [
            'resend_hash' => $resend_hash
        ];
        $aff->set($set);
        $aff->save();

        $resend_link = $this->domain_link . 'resend/' .
            $aff->token . '/' . $resend_hash;

        Session::set_flash("resend_link", $resend_link);

        // Unfortunatelly it has to be done like that,
        // because current used function for messages
        // show all the messages with tags - we dont want that
        
        $resend_message_middle = _('try to resend');
        
        Session::set_flash("resend_message_start", $resend_message_start);
        Session::set_flash("resend_message_middle", $resend_message_middle);
        Session::set_flash("resend_message_end", $resend_message_end);
    }
    
    /**
     *
     * @return bool
     */
    public function aff_activation_type_to_process(): bool
    {
        $proper_activation_types = [
            Helpers_General::ACTIVATION_TYPE_OPTIONAL,
            Helpers_General::ACTIVATION_TYPE_REQUIRED
        ];
        
        if (in_array((int)$this->whitelabel['aff_activation_type'], $proper_activation_types)) {
            return true;
        }
        
        return false;
    }

    /**
     *
     * @return int
     */
    public function send_email_to_manager(): int
    {
        $title = $this->get_email_title_to_manager();

        $manager_email = $this->get_whitelabel_email();

        $from_email = $this->get_from_email();

        $email_to_manager_content_as_html = $this->get_content_of_email_to_manager(true);
        $email_to_manager_content_as_text = $this->get_content_of_email_to_manager(false);

        $html_body = sprintf(
            $email_to_manager_content_as_html,
            $this->get_link_to_manager_list()
        );
        $alt_body = sprintf(
            $email_to_manager_content_as_text,
            $this->get_link_to_manager_list()
        );

        \Package::load('email');
        $email = Email::forge();
        $email->from($from_email);
        $email->to($manager_email);
        $email->subject($title);
        $email->html_body($html_body);
        $email->alt_body($alt_body);

        try {
            $email->send();
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );

            self::RESULT_EMAIL_NOT_SENT;
        };

        return self::RESULT_EMAIL_SENT;
    }

    private function send_registration_email(WhitelabelAff $new_aff): int
    {
        if ($this->aff_activation_type_to_process()) {
            $new_salt = $this->get_salt();
            $time_date = $this->get_time_date();
            $activation_hash = Lotto_Security::generate_time_hash($new_salt, $time_date);

            $set_activation = [
                "activation_hash" => $activation_hash,
                "activation_valid" => $time_date->add(new DateInterval("P1D"))->format("Y-m-d H:i:s")
            ];
            
            $new_aff->set($set_activation);
            $new_aff->save();

            // activation e-mail
            $activation_link = $this->domain_link . "activation/" .
                $new_aff->token . '/' . $activation_hash;
            $this->set_activation_link($activation_link);
            
            $result = $this->send_email(self::EMAIL_TYPE_REGISTRATION);
            
            switch ($result) {
                case self::RESULT_OK:
                    $message = _(
                        "Your account is not active yet. " .
                        "To activate your account please confirm your " .
                        "e-mail address by following the confirmation " .
                        "link we have sent you to your e-mail."
                    );
                    Session::set_flash("message", ["success", $message]);
                    break;
                case self::RESULT_EMAIL_NOT_SENT:
                    $resend_message_start = _(
                        'Your account is created, however we\'ve encountered ' .
                        'problems while sending the activation e-mail. ' .
                        'Please '
                    );
                    $resend_message_end = _(
                        ' the activation e-mail or contact us for manual activation.'
                    );
                    $this->create_resend_message(
                        $new_aff,
                        $resend_message_start,
                        $resend_message_end
                    );
                    break;
            }
            
            return $result;
        }
        
        return self::RESULT_OK;
    }
    
    /**
     * @return int
     */
    public function process_form(): int
    {
        if (Input::post("register") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        if (!\Security::check_token()) {
            $this->inside->set('errors', ['login' => _('Security error! Please try again.')]);
            return self::RESULT_SECURITY_ERROR;
        }

        if (!Helpers_General::is_development_env() &&
            !Lotto_Security::check_captcha()
        ) {
            $this->inside->set('errors', ['login' => _("Wrong captcha.")]);
            return self::RESULT_WRONG_CAPTCHA;
        }

        if (!Helpers_General::is_development_env() &&
            !Lotto_Security::check_IP()
        ) {
            $this->inside->set('errors', ['login' => _('Too many register attempts! Please try again later.')]);
            return self::RESULT_TOO_MANY_ATTEMPTS;
        }

        $final_result = self::RESULT_OK;
        
        $validated_form = $this->validate_form();
        
        if ($validated_form->run()) {
            $defaultLangId = Helpers_General::get_default_language_id();

            $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $this->whitelabel,
                $validated_form->validated("register.email"),
                $validated_form->validated("register.login")
            );

            if (is_null($result)) {
                $this->fileLoggerService->error(
                    "Something wrong with DB."
                );
                $message = _('There is a problem on server! Please contact us');
                $this->inside->set('errors', ['login' => $message]);
                return self::RESULT_WITH_ERRORS;
            }

            $affCount = $result[0]['count'];
            
            if ((int)$affCount > 0) {
                $error_msg = _(
                    "The email address and/or login you have " .
                    "provided are already in use."
                );
                $errors = ['register.email' => $error_msg];
                $this->inside->set("errors", $errors);

                return self::RESULT_WITH_ERRORS;
            }
            
            $this->set_new_aff_login($validated_form->validated("register.login"));
            $this->set_new_aff_email($validated_form->validated("register.email"));
            
            $salt = $this->get_salt();
            $hash = Lotto_Security::generate_hash(
                $validated_form->validated("register.password"),
                $salt
            );

            $token = Lotto_Security::generate_aff_token($this->whitelabel['id']);
            $newSubAffiliateToken = Lotto_Security::generate_aff_token($this->whitelabel['id']);
            $this->set_new_aff_token($token);

            // This will be default group ID set, which in fact is group
            // defied in whitelabel table in database
            $whitelabelAffGroupId = null;

            // These values depend on different circumstances
            $this->aff_active_status = 0;
            $isConfirmed = 0;
            $isAccepted = 0;
            
            $affActivationType = (int)$this->whitelabel['aff_activation_type'];
            
            switch ($affActivationType) {
                case Helpers_General::ACTIVATION_TYPE_NONE:
                    $this->aff_active_status = 1;
                    $isConfirmed = 0;
                    break;
                case Helpers_General::ACTIVATION_TYPE_OPTIONAL:
                    $this->aff_active_status = 1;
                    $isConfirmed = 0;
                    break;
                case Helpers_General::ACTIVATION_TYPE_REQUIRED:
                    $this->aff_active_status = 0;
                    $isConfirmed = 0;
                    $isAccepted = 0;
                    break;
            }
            
            $affAutoAccept = (int)$this->whitelabel['aff_auto_accept'];
            if ($affAutoAccept === 1) {
                $isAccepted = 1;
            }

            $affiliateToken = $this->affService->getPropertyFromCookie(
                Helpers_General::COOKIE_AFF_NAME
            );

            $affiliateParentId = null;
            if ($affiliateToken) {
                $refAffiliate = $this->whitelabelAffRepository->findAffiliateByTokenOrSubToken($this->whitelabel, $affiliateToken);

                if (!empty($refAffiliate['id'])) {
                    $affiliateParentId = $refAffiliate['id'];
                }
            }

            $newAffiliate = $this->whitelabelAffRepository->insert(
                $this->whitelabel,
                $affiliateParentId,
                $this->get_new_aff_token(),
                $newSubAffiliateToken,
                $defaultLangId,
                $whitelabelAffGroupId,
                $this->aff_active_status,
                $isConfirmed,
                $isAccepted,
                $this->get_new_aff_login(),
                $this->get_new_aff_email(),
                $hash,
                $salt
            );

            Session::set("aff.name", $this->get_new_aff_login());
            Session::set("aff.hash", $hash);

            $final_result = $this->send_registration_email($newAffiliate);

            // Only if registration email was successfully sent to affiliate
            // system should also send info mail to manager!
            if ($final_result === self::RESULT_OK) {
                // Not react on result, but within function there is log function
                // call on Failure of send mail
                $result = $this->send_email_to_manager();
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);

            return self::RESULT_WITH_ERRORS;
        }

        return $final_result;
    }

    /**
     *
     * @param string $token
     * @param string $hash
     * @return int
     */
    public function activation(string $token = "", string $hash = ""): int
    {
        if (empty($token)) {
            $this->fileLoggerService->error(
                "The token is empty."
            );
            return self::RESULT_EMPTY_TOKEN;
        }
        
        if (empty($hash)) {
            $this->fileLoggerService->error(
                "The hash is empty."
            );
            return self::RESULT_EMPTY_HASH;
        }
        
        $affs = Model_Whitelabel_Aff::find([
            "where" => [
                "whitelabel_id" => (int)$this->whitelabel['id'],
                "token" => $token
            ]
        ]);
        
        if (!($affs !== null &&
            count($affs) > 0 &&
            (int)$affs[0]['whitelabel_id'] === (int)$this->whitelabel['id'] &&
            (int)$affs[0]['is_deleted'] === 0 &&
            !empty($hash) &&
            (string)$affs[0]['activation_hash'] === (string)$hash)
        ) {
            $message = _(
                "Incorrect activation link. " .
                "Please contact us for manual activation."
            );
            Session::set_flash("message", ["danger", $message]);
            
            return self::RESULT_WRONG_LINK;
        }
        
        $aff = $affs[0];

        $activation_valid = new DateTime(
            $aff['activation_valid'],
            new DateTimeZone("UTC")
        );

        $now = new DateTime("now", new DateTimeZone("UTC"));

        if ($activation_valid < $now) {
            $time_date = $this->get_time_date();
            $resend_time_hash = Lotto_Security::generate_time_hash(
                $aff['salt'],
                $time_date
            );
            
            $set = [
                'resend_hash' => $resend_time_hash
            ];
            $aff->set($set);
            $aff->save();
            
            $resend_link = $this->domain_link . 'resend/' .
                $aff->token . '/' . $resend_time_hash;

            Session::set_flash("resend_link", $resend_link);
            
            // Unfortunatelly it has to be done like that,
            // because current used function for messages
            // show all the messages with tags - we dont want that
            $resend_message_start = _(
                'Your activation link has expired. Please '
            );
            $resend_message_middle = _('try to resend');
            $resend_message_end = _(
                ' the activation e-mail or contact us for manual activation.'
            );
            Session::set_flash("resend_message_start", $resend_message_start);
            Session::set_flash("resend_message_middle", $resend_message_middle);
            Session::set_flash("resend_message_end", $resend_message_end);
            
            return self::RESULT_ACTIVATION_LINK_EXPIRED;
        }
        
        if (!((int)$aff['is_active'] === 0 ||
            (int)$aff['is_confirmed'] === 0)
        ) {
            $message = _(
                "Your account has been activated before. " .
                "Please login to access your account."
            );
            Session::set_flash("message", ["danger", $message]);

            return self::RESULT_ALREADY_ACTIVATED;
        }
        
        $set = [
            'is_active' => 1,
            'is_confirmed' => 1,
            //'last_update' => $now->format("Y-m-d H:i:s")
        ];
        
        $aff->set($set);
        $aff->save();

        if ($this->aff_activation_type_to_process()) {
            $message = _("Thank you for choosing us! Your account is now active.");
            Session::set_flash("activation_message", $message);
        }
        
        Session::set("aff.name", $aff['login']);
        Session::set("aff.hash", $aff['hash']);
        Session::set("aff.remember", 0);

        Lotto_Security::reset_IP();

        return self::RESULT_OK;
    }
    
    /**
     *
     * @param string $token
     * @param string $hash
     * @return int
     */
    public function resend_email(string $token = "", string $hash = ""): int
    {
        if (empty($token)) {
            $this->fileLoggerService->error(
                "The token is empty."
            );
            return self::RESULT_EMPTY_TOKEN;
        }
        
        if (empty($hash)) {
            $this->fileLoggerService->error(
                __CLASS__,
                __LINE__,
                "The hash is empty."
            );
            return self::RESULT_EMPTY_HASH;
        }
        
        $affs = Model_Whitelabel_Aff::find([
            "where" => [
                "whitelabel_id" => (int)$this->whitelabel['id'],
                "token" => $token
            ]
        ]);
        
        if (!($affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]['whitelabel_id'] === (int)$this->whitelabel['id'] &&
                (int)$affs[0]['is_deleted'] === 0 &&
                (int)$affs[0]['is_active'] === 0 &&
                !empty($hash) &&
                (string)$affs[0]['resend_hash'] === (string)$hash)
        ) {
            $message = _(
                "Incorrect resend link. Please contact us for manual activation."
            );
            
            Session::set_flash("message", ["danger", $message]);

            return self::RESULT_WRONG_LINK;
        }
        
        $now = new DateTime("now", new DateTimeZone("UTC"));
        
        $aff = $affs[0];
        $last = null;
        
        if (!empty($aff['resend_last'])) {
            $last = new DateTime($aff['resend_last'], new DateTimeZone("UTC"));
            $last->add(new DateInterval("P1D"));
        }
        
        if ($last != null || $last > $now) {
            $message = _(
                "You can resend the activation link once per 24 hours. " .
                "Please try again later or contact us."
            );
            Session::set_flash("message", ["danger", $message]);

            return self::RESULT_OK;
        }
        
        $time_hash = Lotto_Security::generate_time_hash($aff['salt'], $now);
        
        $set = [
            "activation_hash" => $time_hash,
            "resend_last" => $now->format("Y-m-d H:i:s"),
            "activation_valid" => $now->add(new DateInterval("P1D"))->format("Y-m-d H:i:s")
        ];
        
        $aff->set($set);
        $aff->save();

        // activation e-mail
        $activation_link = $this->domain_link . "activation/" .
            $aff->token . '/' . $time_hash;
        $this->set_activation_link($activation_link);
        
        $result = $this->send_email(self::EMAIL_TYPE_RESEND);
            
        switch ($result) {
            case self::RESULT_OK:
                break;
            case self::RESULT_ACTIVATION:
                $message = _(
                    "Your account is still not active. " .
                    "To activate your account please confirm your " .
                    "e-mail address by following the confirmation " .
                    "link we have sent you to your e-mail."
                );
                Session::set_flash("message", ["success", $message]);
                break;
            case self::RESULT_EMAIL_NOT_SENT:
                $message = _(
                    "We have encountered an error while trying to send the message. " .
                    "Please contact us to activate your account."
                );
                Session::set_flash("message", ["danger", $message]);
                break;
        }
        
        return $result;
    }
    
    /**
     *
     * @param int $type
     * @return int
     */
    private function send_email(int $type): int
    {
        $from_email = $this->get_from_email();
        $whitelabel_name = $this->get_whitelabel_name();
        $new_aff_email = $this->get_new_aff_email();

        $title = $this->get_registration_title();

        $email_registration_as_html = $this->get_content_of_registration_email(true);
        $email_registration_as_text = $this->get_content_of_registration_email(false);

        $html_body = sprintf(
            $email_registration_as_html,
            $this->get_activation_link()
        );
        $alt_body = sprintf(
            $email_registration_as_text,
            $this->get_activation_link()
        );

        \Package::load('email');
        $email = Email::forge();
        $email->from($from_email, $whitelabel_name);
        $email->to($new_aff_email);
        $email->subject($title);
        $email->html_body($html_body);
        $email->alt_body($alt_body);

        try {
            $email->send();
            
            if ($type === self::EMAIL_TYPE_RESEND &&
                $this->aff_activation_type_to_process()
            ) {
                return self::RESULT_ACTIVATION;
            }
        } catch (\Exception $e) {
            return self::RESULT_EMAIL_NOT_SENT;
        }

        return self::RESULT_OK;
    }
}
