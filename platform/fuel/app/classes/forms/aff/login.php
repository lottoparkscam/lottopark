<?php

use Fuel\Core\Validation;
use Repositories\Aff\WhitelabelAffRepository;
use Services\Logs\FileLoggerService;

/**
 * @deprecated
 * Description of Forms_Aff_Login
 */
class Forms_Aff_Login extends Forms_Main
{
    const RESULT_AFF_IS_NOT_ACTIVE = 100;

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
     * @var null|array
     */
    private $user = null;

    private FileLoggerService $fileLoggerService;

    private WhitelabelAffRepository $whitelabelAffRepository;

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
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
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
     * @return array|null
     */
    public function get_user(): ?array
    {
        return $this->user;
    }

    /**
     *
     * @param array $user
     * @return null|Model_Whitelabel_Aff
     */
    private function get_auser(array $user = null): ?Model_Whitelabel_Aff
    {
        // Should not be happend
        if (is_null($user)) {
            return null;
        }

        $auser = Model_Whitelabel_Aff::find_by_pk($user['id']);

        return $auser;
    }

    /**
     *
     * @return int
     */
    public function check_and_update_credentials(): int
    {
        $credential_log = Session::get("aff.name");
        $credential_hash = Session::get("aff.hash");

        // This time only check if user exists and isn't disabled
        $this->user = Model_Whitelabel_Aff::check_aff_credentials_hashed(
            $credential_log,
            $credential_hash,
            false,
            false
        );

        if (!is_null($this->user)) {
            $aff_activation_type = (int)$this->whitelabel["aff_activation_type"];

            switch ($aff_activation_type) {
                case Helpers_General::ACTIVATION_TYPE_NONE:
                    break;
                case Helpers_General::ACTIVATION_TYPE_OPTIONAL:
                    break;
                case Helpers_General::ACTIVATION_TYPE_REQUIRED:
                    // If login is OK, system should check if aff has is_active flag set to 1
                    $user_active_and_confirmed = Model_Whitelabel_Aff::check_aff_credentials_hashed(
                        $credential_log,
                        $credential_hash,
                        true,
                        true
                    );

                    if (empty($user_active_and_confirmed)) {
                        $this->user = null;

                        return self::RESULT_AFF_IS_NOT_ACTIVE;
                    }

                    $this->user = $user_active_and_confirmed;
                    break;
            }
        } else {
            try {
                if (!empty($credential_log) && !empty($credential_hash)) {
                    $throw_msg = "Null user data for given credentials " .
                        "name/email:  " .
                        $credential_log .
                        " and hash: " .
                        $credential_hash;
                    throw new Exception($throw_msg);
                }
                return self::RESULT_GO_FURTHER;
            } catch (Exception $e) {
                // At this moment this type of message should not be logged
                $this->fileLoggerService->warning(
                    $e->getMessage()
                );
                return self::RESULT_WRONG_CREDENTIALS;
            }
        }

        // In the case that everything is OK
        // some data in DB should be updated for such user!
        $now = new DateTime("now", new DateTimeZone("UTC"));

        $country = $this->user['last_country'];
        if ($this->user['last_ip'] != Lotto_Security::get_IP()) {
            $geo_ip = Lotto_Helper::get_geo_IP_record(Lotto_Security::get_IP());
            $country = null;
            if ($geo_ip !== false) {
                $country = $geo_ip->country->isoCode;
            }
        }

        $auser = $this->get_auser($this->user);

        $auser->set([
            'last_active' => $now->format("Y-m-d H:i:s"),
            'last_ip' => Lotto_Security::get_IP(),
            'last_country' => $country
        ]);
        $auser->save();

        return self::RESULT_OK;
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
            ->add_rule('max_length', $this->options['l_name_max_length']);

        if (!filter_var(Input::post('login.name'), FILTER_VALIDATE_EMAIL)) {
            $validation->field('login.name')->add_rule('valid_string', ['alpha', 'utf8', 'numeric', 'dashes']);
        }

        $validation->add("login.password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', $this->options['l_pass_min_length']);

        $validation->add("login.remember", _("Remember me"))
            ->add_rule('match_value', $this->options['l_rem_match']);

        return $validation;
    }

    public function process_form(&$view): int
    {
        if (Input::post("login") === null) {
            return self::RESULT_GO_FURTHER;
        }

        if (!\Security::check_token()) {
            $view->set_global('errors', ['login' => _('Security error! Please try again.')]);
            return self::RESULT_SECURITY_ERROR;
        }

        if (
            !Helpers_General::is_development_env() &&
            !Lotto_Security::check_captcha()
        ) {
            $view->set_global('errors', ['login' => _("Wrong captcha.")]);
            return self::RESULT_WRONG_CAPTCHA;
        }

        if (!Lotto_Security::check_IP()) {
            $view->set_global('errors', ['login' => _('Too many login attempts! Please try again later.')]);
            return self::RESULT_TOO_MANY_ATTEMPTS;
        }

        $validatedForm = $this->validate_form();

        if ($validatedForm->run()) {
            $loginOrEmail = $validatedForm->validated('login.name');
            $password = $validatedForm->validated('login.password');
            $remember = $validatedForm->validated('login.remember');

            $aff = $this->whitelabelAffRepository
                ->findAffiliateAccountByLoginOrEmail($loginOrEmail, $this->whitelabel['id']);

            if (empty($aff)) {
                $view->set_global('errors', [
                    'login' => _('Wrong credentials! Please try again.')
                ]);
                return self::RESULT_WRONG_CREDENTIALS;
            }

            $passwordHash = Lotto_Security::generate_hash($password, $aff['salt']);
            if ($passwordHash !== $aff['hash']) {
                $view->set_global('errors', [
                    'login' => _('Wrong credentials! Please try again.')
                ]);
                return self::RESULT_WRONG_CREDENTIALS;
            }

            $affActivationType = (int)$this->whitelabel['aff_activation_type'];
            $affActivationIsRequired = $affActivationType === Helpers_General::ACTIVATION_TYPE_REQUIRED;
            $isNotActiveOrNotConfirmed = !(bool)$aff['is_active'] || !(bool)$aff['is_confirmed'];
            if ($affActivationIsRequired && $isNotActiveOrNotConfirmed) {
                $view->set_global('errors', [
                    'login' => _('Your account is not active. Please follow the activation link provided in the e-mail.')
                ]);

                return self::RESULT_AFF_IS_NOT_ACTIVE;
            }

            Lotto_Security::reset_IP();

            Session::set('aff.remember', $remember == 1 ? 1 : 0);
            Session::set('aff.name', $loginOrEmail);
            Session::set('aff.hash', $passwordHash);
        } else {
            $view->set_global('errors', ['login' => _('Wrong credentials! Please try again.')]);
            return self::RESULT_WRONG_CREDENTIALS;
        }

        return self::RESULT_OK;
    }
}
