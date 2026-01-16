<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

/**
 * I left last part of the name joined because I don't think that
 * moving class to another subfolder is not needed for that purpose,
 * however I don't want to remove password phrase from the name, because
 * in fact the main purpose and functionality of that class is strictly connected
 * with password!
 *
 * Description of Forms_Wordpress_Myaccount_Remove_Confirmationpassword
 */
final class Forms_Wordpress_Myaccount_Remove_Confirmationpassword extends Forms_Main
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
    protected function validate_form(): Validation
    {
        $validation = Validation::forge("password");

        $validation->add("myaccount_remove.password", _("Confirmation Password"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule('min_length', 6);

        return $validation;
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $errors = [];

        $auser = $this->get_auser();
        if (empty($auser)) {
            try {
                $message_text = _("Unknown error! Please contact us!");
                $message = ['error', $message_text];
                $this->set_messages($message);
                throw new Exception("There is a problem with DB!");
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }
            
            return self::RESULT_WITH_ERRORS;
        }

        if (empty(Input::post("myaccount_remove.password"))) {
            return self::RESULT_WITH_ERRORS;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $hash = null;
            $dbuser = Model_Whitelabel_User::check_user_credentials(
                $auser['email'],
                $validated_form->validated("myaccount_remove.password"),
                $hash
            );

            if (is_null($dbuser)) {
                $errors = $this->get_errors();
                $error_text = _('Wrong Confirmation Password! Please try again.');
                $error = ['myaccount_remove.password' => $error_text];
                $errors_merged = array_merge($errors, $error);
                $this->set_errors($errors_merged);
                
                return self::RESULT_WITH_ERRORS;
            }
            
            $now = new DateTime("now", new DateTimeZone("UTC"));
            $auser->set([
                'last_update' => $now->format("Y-m-d H:i:s"),
                'is_deleted' => 1,
                'date_delete' => $now->format("Y-m-d H:i:s")
            ]);
            $auser->save();

            $messages = $this->get_messages();
            $message_text = _(
                "Your account have been successfully deleted " .
                "and you have been successfully logged out!"
            );
            $message = ["success", $message_text];
            $messages_temp = array_merge($messages, $message);
            $this->set_messages($messages_temp);

            Session::delete("user");
            Session::set("message", $message);
            
            // include delete-user hook, after all operations responsible
            // for deletion were done and just before redirection.
            Lotto_Helper::hook_with_globals("user-delete", [
                "user_hook" => $auser,
            ]);
        } else {
            $errors = $this->get_errors();
            $error = Lotto_Helper::generate_errors($validated_form->error());
            $errors_merged = array_merge($errors, $error);
            $this->set_errors($errors_merged);
            
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
