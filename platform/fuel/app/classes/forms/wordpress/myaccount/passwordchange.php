<?php

use Helpers\UserHelper;
use Services\Logs\FileLoggerService;

class Forms_Wordpress_Myaccount_Passwordchange
{
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $messages = [];
    
    /**
     *
     * @var array
     */
    private $errors = [];
    
    /**
     *
     * @var array
     */
    private $auser = [];
    
    /**
     *
     *
     * @param array $auser
     */
    public function __construct($auser)
    {
        $this->auser = $auser;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @param array $messages
     */
    public function set_messages($messages)
    {
        $this->messages = $messages;
    }

    /**
     *
     * @return array
     */
    public function get_messages()
    {
        return $this->messages;
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
     * @param array $auser
     */
    public function set_auser($auser)
    {
        $this->auser = $auser;
    }
    
    /**
     * @return Validation object
     */
    private function get_prepared_form()
    {
        $val = Validation::forge("passwordchange");
        
        $val->add("profile.password", _("Current password"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule('min_length', 6);
        
        $val->add("profile.npassword", _("New password"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule('min_length', 6);
        
        $val->add("profile.rpassword", _("Repeat new password"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule('min_length', 6)
            ->add_rule('match_field', "profile.npassword");
                
        return $val;
    }
    
    /**
     *
     * @return null
     */
    public function process_form()
    {
        $errors = [];
        
        $auser = $this->get_auser();
        if (empty($auser)) {
            try {
                $message = ['error', _("Unknown error! Please contact us!")];
                $this->set_messages($message);
                throw new Exception("There is a problem with DB!");
            } catch (Exception $e) {
                $this->fileLoggerService->error($e->getMessage());
            }
            return ;
        }
        
        $val = $this->get_prepared_form();
        
        if ($val->run()) {
            $hash = null;
            $dbuser = Model_Whitelabel_User::check_user_credentials($auser['email'], $val->validated("profile.password"), $hash);

            if (!is_null($dbuser)) {
                $now = new DateTime("now", new DateTimeZone("UTC"));
                $newsalt = Lotto_Security::generate_salt();
                $hash = Lotto_Security::generate_hash($val->validated("profile.npassword"), $newsalt);
                $auser->set([
                    'salt' => $newsalt,
                    'hash' => $hash,
                    'last_update' => $now->format("Y-m-d H:i:s")
                ]);
                $auser->save();
                
                $messages = $this->get_messages();
                $message = ["success", _("Your password has been changed.")];
                $messages_temp = array_merge($messages, $message);
                $this->set_messages($messages_temp);

                UserHelper::setUserSession(
                    $auser['id'],
                    $auser['token'],
                    $auser['hash'],
                    $auser['email']
                );
            } else {
                $errors = $this->get_errors();
                $error = ['profile.password' => _('Wrong current password! Please try again.')];
                $errors_temp = array_merge($errors, $error);
                $this->set_errors($errors_temp);
            }
        } else {
            $errors = $this->get_errors();
            $error = Lotto_Helper::generate_errors($val->error());
            $errors_temp = array_merge($errors, $error);
            $this->set_errors($errors_temp);
        }
    }
}
