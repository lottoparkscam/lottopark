<?php

use Fuel\Core\Validation;
use Fuel\Core\Session;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Services\Logs\FileLoggerService;

/** @deprecated */
class Forms_Login extends Forms_Main
{
    const RESULT_EMPTY_SALT = 100;
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var string
     */
    private $source = "";

    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var array
     */
    private $options = [
        'l_name_min_length' => 3,
        'l_name_max_length' => 30,
        'l_pass_min_length' => 6,
        'l_rem_match' => 1,
    ];

    /**
     *
     * @param string $source
     */
    public function __construct($source)
    {
        $this->source = $source;

        $this->fileLoggerService = Container::get(FileLoggerService::class);
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("login.name", _("Login"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', $this->options['l_name_min_length'])
            ->add_rule('max_length', $this->options['l_name_max_length'])
            ->add_rule('valid_string', ['alpha', 'utf8', 'numeric', 'dashes']);

        $validation->add("login.password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', $this->options['l_pass_min_length']);

        $validation->add("login.remember", _("Remember me"))
            ->add_rule('match_value', $this->options['l_rem_match']);

        return $validation;
    }

    /**
     *
     * @param View $view
     * @param array $whitelabel
     * @return int
     */
    public function process_form(&$view, $whitelabel = []): int
    {
        if (Input::post("login") === null) {
            return self::RESULT_GO_FURTHER;
        }

        if (!Security::check_token()) {
            $view->set_global('errors', ['login' => _('Security error! Please try again.')]);
            return self::RESULT_SECURITY_ERROR;
        }

        if (!Lotto_Security::check_IP()) {
            $view->set_global('errors', ['login' => _('Too many login attempts! Please try again later.')]);
            return self::RESULT_TOO_MANY_ATTEMPTS;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $check_credentials = false;
            $login_name = $validated_form->validated("login.name");
            $login_password = $validated_form->validated("login.password");
            $login_remember = $validated_form->validated("login.remember");
            $hash = null;

            switch ($this->source) {
                case "whitelabel":
                    $check_credentials = Lotto_Security::check_whitelabel_credentials(
                        $login_name,
                        $login_password
                    );
                    if ($check_credentials) {
                        Session::set("source", 'whitelabel');
                        break;
                    }
                    // no break
                case "admin":
                    $check_credentials = Model_Setting::check_admin_credentials(
                        $login_name,
                        $login_password
                    );
                    if ($check_credentials) {
                        if ($this->source === "whitelabel") {
                            $this->source = "admin";
                        }
                        Session::set("source", 'admin');
                    }
                    break;
                default:
                    break;
            }

            if (!$check_credentials) {
                $view->set_global('errors', ['login' => _('Wrong credentials! Please try again.')]);
                return self::RESULT_WRONG_CREDENTIALS;
            }

            switch ($this->source) {
                case "admin":
                    $admin_salt = Model_Setting::get_admin_salt();
                    // This should not happen - it means that there is wrong settings in DB
                    if (empty($admin_salt)) {
                        return self::RESULT_EMPTY_SALT;
                    }
                    $hash = Lotto_Security::generate_hash(
                        $login_password,
                        $admin_salt
                    );
                    break;
                case "whitelabel":
                    if (empty($whitelabel['salt'])) {
                        $view->set_global(
                            'errors',
                            [
                                'login' => _(
                                    'There is something wrong with credentials. ' .
                                        'Please contact administrator.'
                                )
                            ]
                        );
                        return self::RESULT_EMPTY_SALT;
                    }
                    $hash = Lotto_Security::generate_hash(
                        $login_password,
                        $whitelabel['salt']
                    );
                    break;
            }

            Lotto_Security::reset_IP();

            Session::set($this->source . ".remember", $login_remember == 1 ? 1 : 0);
            Session::set($this->source . ".name", $login_name);
            Session::set($this->source . ".hash", $hash);
        } else {
            $view->set_global(
                'errors',
                [
                    'login' => _('Wrong credentials! Please try again.')
                ]
            );
            return self::RESULT_WRONG_CREDENTIALS;
        }

        return self::RESULT_OK;
    }
}
