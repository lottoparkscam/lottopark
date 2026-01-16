<?php

use Fuel\Core\Validation;
use Helpers\UserHelper;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

class Forms_Wordpress_User_Lostpassword_New extends Forms_Main
{
    const RESULT_LINK_EXPIRED = 100;

    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $wlang = [];
    
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
    //put your code here
    
    /**
     *
     * @var null|Model_Whitelabel_User
     */
    private $user = null;
    
    /**
     *
     * @var null|int
     */
    private $token = null;
    
    /**
     *
     * @var null|string
     */
    private $hash = null;
    
    /**
     *
     * @var null|string
     */
    private $new_salt = null;
    
    /**
     *
     * @var null|string
     */
    private $new_hash = null;
    
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
        $this->whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $this->wlang = LanguageHelper::getCurrentWhitelabelLanguage();
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
     *
     * @return string|null
     */
    public function get_token():? string
    {
        return $this->token;
    }

    /**
     *
     * @return string|null
     */
    public function get_hash():? string
    {
        return $this->hash;
    }

    /**
     *
     * @param string $token
     * @return \Forms_Wordpress_User_Lostpassword_New
     */
    public function set_token(string $token = null): Forms_Wordpress_User_Lostpassword_New
    {
        $this->token = $token;
        return $this;
    }

    /**
     *
     * @param string $hash
     * @return \Forms_Wordpress_User_Lostpassword_New
     */
    public function set_hash(string $hash = null): Forms_Wordpress_User_Lostpassword_New
    {
        $this->hash = $hash;
        return $this;
    }

        
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $validation->add("lost.password", _("New password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $validation->add("lost.rpassword", _("Repeat new password"))
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6)
            ->add_rule('match_field', "lost.password");
        
        return $validation;
    }
    
    /**
     *
     * @param string $email
     * @return null|array
     */
    private function get_users():? array
    {
        $users = Model_Whitelabel_User::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => $this->token
            ]
        ]);
        
        return $users;
    }
    
    /**
     *
     * @return void
     */
    private function generate_new_salt(): void
    {
        $this->new_salt = Lotto_Security::generate_salt();
    }
    
    /**
     *
     * @param string $password
     * @return void
     */
    private function generate_new_hash(string $password): void
    {
        $this->new_hash = Lotto_Security::generate_hash($password, $this->new_salt);
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
                'salt' => $this->new_salt,
                'hash' => $this->new_hash,
                'last_update' => $now->format("Y-m-d H:i:s"),
                'lost_hash' => null,
                'is_confirmed' => 1
            ];

            $this->user->set($set);
            $this->user->save();

            // Send welcome email to user
            $email_data = [
            ];

            $email_helper = new Helpers_Mail($this->whitelabel, $this->user);
            $email_helper->send_welcome_email(
                $this->user['sent_welcome_mail'],
                $this->user['email'],
                $this->wlang['code'],
                $email_data
            );
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
     * @return int
     */
    public function process_form(): int
    {
        if (empty($this->token) || empty($this->hash)) {
            return self::RESULT_GO_FURTHER;
        }
        
        $users = $this->get_users();
        
        if ($users === null ||
            count($users) === 0 ||
            (int)$users[0]['whitelabel_id'] !== (int)$this->whitelabel['id'] ||
            (string)$this->hash !== (string)$users[0]['lost_hash']
        ) {
            $error_message = _(
                "Incorrect lost password link. Please try again or contact " .
                "us for manual password change."
            );
            Session::set("message", ["error", $error_message]);
            
            return self::RESULT_INCORRECT_USER;
        }
        
        $this->user = $users[0];
        $lost_last = new DateTime($this->user['lost_last'], new DateTimeZone("UTC"));
        $lost_last->add(new DateInterval("P1D"));
        $now = new DateTime("now", new DateTimeZone("UTC"));
        
        if ($now > $lost_last) {
            $url = lotto_platform_get_permalink_by_slug('lostpassword');
            $error_text = _(
                'This password reset link has expired. Please start the ' .
                'password reset process <a href="%s">once again</a>.'
            );
            $error_message = sprintf($error_text, $url);
            Session::set("message", ["error", $error_message]);
            
            return self::RESULT_LINK_EXPIRED;
        }
        
        Lotto_Settings::getInstance()->set("lostpasswordstep", 2);
        Lotto_Settings::getInstance()->set("lostpasswordusertoken", $this->token);

        Lotto_Settings::getInstance()->set("lostpasswordnow", $now);
        
        return self::RESULT_GO_FURTHER;
    }
    
    /**
     *
     * @return int
     */
    public function process_form_second_step(): int
    {
        if (Input::post("lost") === null) {
            return self::RESULT_GO_FURTHER;
        }
        
        if (!\Security::check_token()) {
            $this->errors = ['lost' => _('Security error! Please try again.')];
            return self::RESULT_WITH_ERRORS;
        }
        
        $validated_form = $this->validate_form();
        
        if ($validated_form->run()) {
            $this->generate_new_salt();
            
            $this->generate_new_hash($validated_form->validated("lost.password"));
            
            $token = Lotto_Settings::getInstance()->get("lostpasswordusertoken");
            $this->set_token($token);
            
            $users = $this->get_users();
            if ($users == null || count($users) === 0) {
                $error_message = _(
                    "Incorrect lost password link. Please try again or contact " .
                    "us for manual password change."
                );
                Session::set("message", ["error", $error_message]);

                return self::RESULT_INCORRECT_USER;
            }
            
            $this->user = $users[0];
            
            $now = Lotto_Settings::getInstance()->get("lostpasswordnow");
            $is_saved = $this->save_user_data($now);
            
            if (!$is_saved) {
                $error_message = _(
                    "We have encountered an error while trying to save data. " .
                    "Please try again or contact us."
                );
                Session::set("message", ["error", $error_message]);
                
                return self::RESULT_DB_ERROR;
            }
            
            $success_message = _(
                "Your password has been changed. You have been " .
                "successfully logged in!"
            );
            Session::set("message", ["success", $success_message]);

            UserHelper::setUserSession(
                $this->user['id'],
                $this->user['token'],
                $this->user['hash'],
                $this->user['email']
            );
        
            Lotto_Security::reset_IP();
        } else {
            $this->errors = Lotto_Helper::generate_errors($validated_form->error());
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
