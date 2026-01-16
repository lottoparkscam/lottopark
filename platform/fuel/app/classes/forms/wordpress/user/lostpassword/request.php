<?php

use Fuel\Core\Validation;
use Forms\Wordpress\Forms_Wordpress_Email;
use Models\Whitelabel;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

class Forms_Wordpress_User_Lostpassword_Request extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

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
    
    /**
     *
     * @var null|Model_Whitelabel_User
     */
    private $user = null;
    
    /**
     *
     * @var string
     */
    private $time_hash = "";
    
    /**
     *
     * @var string
     */
    private $link = "";
    
    /**
     * @param array $whitelabel
     */
    public function __construct()
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->errors = Lotto_Settings::getInstance()->get("login_errors");
        if ($this->errors === null) {
            $this->errors = [];
        }
        $this->is_user = Lotto_Settings::getInstance()->get("is_user");
        $this->whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
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

        /** @var Whitelabel $whitelabelModel */
        $whitelabelModel = Container::get('whitelabel');
        if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
            $validation->add("lost.login", _("Login"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        } else {
            $validation->add("lost.email", _("E-mail address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');
        }
        
        return $validation;
    }
    
    /**
     *
     * @param string $email
     * @return null|array
     */
    private function get_users(string $email):? array
    {
        $users = Model_Whitelabel_User::find(
            [
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "is_deleted" => 0,
                    "email" => $email
                ],
                "limit" => 1
            ]
        );
        
        return $users;
    }

    /**
     *
     * @param string $login
     * @return null|array
     */
    private function get_users_by_login(string $login):? array
    {
        $users = Model_Whitelabel_User::find(
            [
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "is_deleted" => 0,
                    "login" => $login
                ],
                "limit" => 1
            ]
        );
        
        return $users;
    }
    
    /**
     *
     * @param \DateTime $now
     */
    private function generate_time_hash(\DateTime $now): void
    {
        if (empty($this->time_hash)) {
            $this->time_hash = Lotto_Security::generate_time_hash(
                $this->user['salt'],
                $now
            );
        }
    }
    
    /**
     *
     * @param \DateTime $now
     * @return bool
     */
    private function save_user_data(\DateTime $now): bool
    {
        try {
            $set = [
                "lost_hash" => $this->time_hash,
                "lost_last" => $now->format("Y-m-d H:i:s")
            ];

            $this->user->set($set);
            $this->user->save();
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
            
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @return string
     */
    private function prepare_link(): string
    {
        $lost_password_slug = lotto_platform_get_permalink_by_slug('auth/lostpassword');
        
        if (empty($lost_password_slug)) {
            return "";
        }
        
        $this->link = $lost_password_slug .
            $this->user->token . '/' .
            $this->time_hash . '/';
        
        return $this->link;
    }
    
    /**
     *
     * @return array
     */
    private function get_email_template(): array
    {
        $whitelabel_language_data = LanguageHelper::getCurrentWhitelabelLanguage();
        
        $email_data = [
            'link' => $this->link
        ];
        
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email(
            'lost-password',
            $whitelabel_language_data['code'],
            $email_data
        );
        
        return $email_template;
    }
    
    /**
     *
     * @param \DateTime $now
     * @return int
     */
    private function prepare_and_send_mail(\DateTime $now): int
    {
        $this->generate_time_hash($now);
        
        $is_saved = $this->save_user_data($now);
        
        if (!$is_saved) {
            $error_message = _(
                "We have encountered an error while trying to save data. " .
                "Please try again or contact us."
            );
            Session::set("message", ["error", $error_message]);
            
            return self::RESULT_DB_ERROR;
        }
        
        // Prepare email data
        $link = $this->prepare_link();
        if (empty($link)) {
            $error_message = _(
                "We have encountered an error while trying to save data. " .
                "Please try again or contact us."
            );
            Session::set("message", ["error", $error_message]);
            
            $error_message = "Link to redirect for user is empty for userID: " .
                $this->user['id'] . " and whitelabelID: " . $this->whitelabel['id'] . "!";
            $this->fileLoggerService->error(
                $error_message
            );
            
            return self::RESULT_WITH_ERRORS;
        }
        
        $from = 'noreply+'.time().'@' . Lotto_Helper::getWhitelabelDomainFromUrl();
        
        $email_template = $this->get_email_template();
        
        $body_alt_text = _(
            "We have received a password reset request for your account. " .
            "To complete the process please follow the link: %1\$s\n\nPlease " .
            "ignore this e-mail if you did not request a password reset."
        );
        $body_alt = sprintf($body_alt_text, $this->link);
        
        \Package::load('email');
        $email = Email::forge();
        $email->from($from, $this->whitelabel['name']);
        $email->to($this->user->email);
        $email->subject($email_template['title']);
        $email->html_body($email_template['body_html']);
        $email->alt_body($email_template['alt_body']);
        
        try {
            $email->send();
            
            $success_message = _(
                "We have sent you an e-mail with password reset link. " .
                "Please follow the link to complete the process."
            );
            
            Session::set("message", ["success", $success_message]);
            
            return self::RESULT_OK;
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
            
            $error_message = _(
                "We've encountered problems while sending the e-mail. " .
                "Please try again later or contact us to reset your password."
            );
            
            Session::set("message", ["error", $error_message]);
            
            return self::RESULT_EMAIL_NOT_SENT;
        }
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        if ($this->is_user || (!$this->is_user && Input::post("lost") == null)) {
            return self::RESULT_GO_FURTHER;
        }
        
        if (!\Security::check_token()) {
            $this->errors = [
                'lost' => _('Security error! Please try again.')
            ];
            
            return self::RESULT_WITH_ERRORS;
        }
        
        if (!(Helpers_General::is_development_env() ||
            Lotto_Security::check_captcha())
        ) {
            $this->errors = [
                'lost' => _('Incorrect captcha! Please try again.')
            ];
            
            return self::RESULT_WITH_ERRORS;
        }
        
        $result = self::RESULT_OK;
        
        $validated_form = $this->validate_form();
        
        if ($validated_form->run()) {
            /** @var Whitelabel $whitelabelModel */
            $whitelabelModel = Container::get('whitelabel');
            if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                $users = $this->get_users_by_login($validated_form->validated("lost.login"));
                if (empty($users) || count($users) === 0) {
                    $this->errors = [
                        'lost.login' => _("We couldn't find your account. Please try another login or contact us.")
                    ];
                    
                    return self::RESULT_WITH_ERRORS;
                }
            } else {
                $users = $this->get_users($validated_form->validated("lost.email"));
                if (empty($users) || count($users) === 0) {
                    $this->errors = [
                        'lost.email' => _("We couldn't find your account. Please try another e-mail or contact us.")
                    ];
                    
                    return self::RESULT_WITH_ERRORS;
                }
            }
            
            $this->user = $users[0];
            $last = null;
            if (!empty($this->user['lost_last'])) {
                $last = new DateTime($this->user['lost_last'], new DateTimeZone("UTC"));
                $last->add(new DateInterval("P1D"));
            }
            $now = new DateTime("now", new DateTimeZone("UTC"));
            
            if (!($last == null || $last <= $now)) {
                $this->errors = [
                    "lost" => _("You can send lost password link once per 24 hours. Please try again later or contact us.")
                ];
                
                return self::RESULT_WITH_ERRORS;
            }
            
            $result = $this->prepare_and_send_mail($now);
        } else {
            $this->errors = Lotto_Helper::generate_errors($validated_form->error());
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return $result;
    }
}
