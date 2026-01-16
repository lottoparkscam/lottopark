<?php

use Fuel\Core\Validation;
use Forms\Wordpress\Forms_Wordpress_Email;
use Helpers\UserHelper;
use Helpers\Wordpress\LanguageHelper;
use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Wordpress_Myaccount_Emailchange
 */
final class Forms_Wordpress_Myaccount_Emailchange extends Forms_Main
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
    private $whitelabel = [];

    /**
     *
     * @var bool
     */
    private $chemail = false;

    /**
     *
     * @var bool
     */
    private $inemail = false;

    /**
     *
     * @param array $user
     * @param array $auser
     * @param array $whitelabel
     */
    public function __construct($user, $auser, $whitelabel)
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->user = $user;
        $this->auser = $auser;
        $this->whitelabel = $whitelabel;
        $this->chemail = false;
        $this->inemail = false;
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
    public function get_user()
    {
        return $this->user;
    }

    /**
     *
     * @param array $user
     */
    public function set_user($user)
    {
        $this->user = $user;
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
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }

    /**
     *
     * @param array $whitelabel
     */
    public function set_whitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return bool
     */
    public function get_chemail()
    {
        return $this->chemail;
    }

    /**
     *
     * @return bool
     */
    public function get_inemail()
    {
        return $this->inemail;
    }

    /**
     *
     * @param bool $chemail
     */
    public function set_chemail($chemail)
    {
        $this->chemail = $chemail;
    }

    /**
     *
     * @param bool $inemail
     */
    public function set_inemail($inemail)
    {
        $this->inemail = $inemail;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge("email");

        $validation->add("profile.current_password", _("Current password"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $validation->add("profile.email", _("E-mail"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        $validation->add("profile.remail", _("Repeat e-mail"))
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("required")
            ->add_rule("valid_email")
            ->add_rule('match_field', "profile.email");

        return $validation;
    }

    /**
     *
     * @return null
     */
    public function process_form()
    {
        $errors = [];

        $user = $this->get_user();
        if (empty($user)) {
            try {
                $message = [
                    'error',
                    _("Unknown error! Please contact us!")
                ];
                $this->set_messages($message);
                throw new Exception("There is a problem with DB!");
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }
            return;
        }

        $auser = $this->get_auser();
        if (empty($auser)) {
            try {
                $message = [
                    'error',
                    _("Unknown error! Please contact us!")
                ];
                $this->set_messages($message);
                throw new Exception("There is a problem with DB!");
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }
            return;
        }

        $whitelabel = $this->get_whitelabel();
        if (empty($whitelabel)) {
            try {
                $message = [
                    'error',
                    _("Unknown error! Please contact us!")
                ];
                $this->set_messages($message);
                throw new Exception("There is a problem with DB!");
            } catch (Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }
            return;
        }

        if (empty(Input::post('profile.email')) ||
            Input::post("profile.email") == $user['email']) {
            return;
        }

        if (empty(Input::post('profile.current_password'))){
            return;
        }

        if (!empty(Input::post('profile.current_password'))) {
            $hash = null;
            $dbuser = Model_Whitelabel_User::check_user_credentials($auser['email'], Input::post('profile.current_password'), $hash);
            if (is_null($dbuser)) {
                return;
            }
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            if ($auser['email'] == $validated_form->validated("profile.email")) {
                $errors = $this->get_errors();
                $error = [
                    'profile.email' => _("This e-mail address is already used by you.")];
                $errors_temp = array_merge($errors, $error);
                $this->set_errors($errors_temp);
                return;
            }

            if ((int)$whitelabel['assert_unique_emails_for_users'] === 1) {
                // Check if new email already exists in DB
                $res = Model_Whitelabel_User::get_count_for_whitelabel_and_email(
                    $whitelabel,
                    $validated_form->validated("profile.email")
                );

                if (is_null($res)) {    // If that situation happen it means that there is a problem with DB
                    try {
                        $message = ['error', _("Unknown error! Please contact us!")];
                        $this->set_messages($message);
                        throw new Exception("There is a problem with DB!");
                    } catch (Exception $e) {
                        $this->fileLoggerService->error(
                            $e->getMessage()
                        );
                    }
                    return;
                }

                $userscnt = $res[0]['count'];

                if ($userscnt != 0) {
                    $errors = $this->get_errors();
                    $error = [
                    'profile.email' => _("This e-mail address is already registered.")
                    ];
                    $errors_temp = array_merge($errors, $error);
                    $this->set_errors($errors_temp);
                    return;
                }
            }

            $username = null;

            $helper = new Helpers_Whitelabel(
                $whitelabel,
                $user,
                $username,
                $validated_form->validated("profile.email"),
                $auser
            );

            $success = $helper->process_cc_method_myaccount_emailchange();

            if (!$success) {
                $errors = $this->get_errors();
                $error = [
                    'profile.email' => _("Failed to update your e-mail address! Please try again or contact us to solve this issue.")
                ];
                $errors_temp = array_merge($errors, $error);
                $this->set_errors($errors_temp);
                return;
            }

            if ((int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                try {
                    UserHelper::updateUserSession('user.email', $validated_form->validated('profile.email'));
                } catch (Throwable $e) {
                    $this->fileLoggerService->error('Cannot update user session. Logging out user... Error message: ' . $e->getMessage());
                    UserHelper::logOutUser();
                }
            }

//            Send activation mail every time when email is changing
                $tdate = new DateTime("now", new DateTimeZone("UTC"));
                $thash = Lotto_Security::generate_time_hash($auser['salt'], $tdate);

                $auser->set(
                    [
                        "activation_hash" => $thash,
                        "activation_valid" => $tdate->add(new DateInterval("P1D"))->format("Y-m-d H:i:s"),
                        "is_confirmed" => 0
                    ]
                );
                $auser->save();
                $this->set_auser($auser);

                /**
                 * Prepare an new Activation email
                 */
                \Package::load('email');
                $email = Email::forge();
                $email->from('noreply+'.time().'@' . Lotto_Helper::getWhitelabelDomainFromUrl(), $whitelabel['name']);
                $email->to($auser->pending_email);

                // send e-mail
                $link = lotto_platform_get_permalink_by_slug('activation');
                $link = $link . $auser->token . '/' . $thash. '?type=email_change';

                $body_alt_text = _(
                    'Thank you for updating your e-mail! ' .
                    'To complete the activation process please follow the link: %1$s'
                );

                $body_alt = sprintf($body_alt_text, $link);

                $wlang = LanguageHelper::getCurrentWhitelabelLanguage();

                $email_data = [
                    'link' => $link
                ];

                $create_email = new Forms_Wordpress_Email($whitelabel);
                $email_template = $create_email->get_email('email-change', $wlang['code'], $email_data);

                $email->subject($email_template['title']);
                $email->html_body($email_template['body_html']);
                $email->alt_body($email_template['alt_body']);

                try {
                    $this->set_inemail(true);
                    $email->send();
                    $this->set_chemail(true);
                } catch (Exception $e) {
                    $this->fileLoggerService->error(
                        $e->getMessage()
                    );

                    $message_text = _(
                        'Your email address has been changed, however we\'ve ' .
                        'encountered problems while sending the activation e-mail. ' .
                        'Please contact us to activate your account.'
                    );

                    $messages = $this->get_messages();
                    $message = ["warning", $message_text];
                    $messages_temp = array_merge($messages, $message);
                    $this->set_messages($messages_temp);

                    $errors = $this->get_errors();
                    $error = ['profile.email' => $message_text];
                    $errors_temp = array_merge($errors, $error);
                    $this->set_errors($errors_temp);
                }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->set_errors($errors);
        }
    }
}
