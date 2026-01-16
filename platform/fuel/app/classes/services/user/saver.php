<?php

use Fuel\Core\Validation;
use Models\Whitelabel;
use Services\Logs\FileLoggerService;

final class Services_User_Saver
{
    use Traits_User_Registration;

    /**
     *
     * @var array $data
     */
    private $data = null;

    /**
     *
     * @var array $whitelabel
     */
    private $whitelabel = null;

    /**
     *
     * @var Validation $validation
     */
    private $validation = null;


    public function __construct($data, $whitelabel)
    {
        $this->data = $data;
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @param bool $with_login
     * @param bool $unique_emails
     */
    private function create_validation_object($with_login, $unique_emails)
    {
        $validation = Validation::forge();

        if ($with_login) {
            $validation->add("login", "Login")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule('required')
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 100)
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        }

        if (!$with_login && $unique_emails) {
            $validation->add("email", "E-mail address")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');
        } else {
            $validation->add("email", "E-mail address")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_email');
        }

        $validation->add("password", "Password")
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $this->validation = $validation;
    }

    public function save_data()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        set_time_limit(600);
        
        $unique_emails = true;
        
        /** @var Whitelabel $whitelabelModel */
        $whitelabelModel = Container::get('whitelabel');
        $with_login = $whitelabelModel->loginForUserIsUsedDuringRegistration();

        if ((int)$this->whitelabel['assert_unique_emails_for_users'] === 0) {
            $unique_emails = false;
        }
        $errors = [];
        $this->create_validation_object($with_login, $unique_emails);

        try {
            DB::start_transaction();

            $limit = 100;

            $counter = 2;

            $batched_array = array_chunk($this->data, $limit);
            foreach ($batched_array as $batch) {
                foreach ($batch as $user_data) {
                    $validated_form = $this->validation;
                    if ($validated_form->run($user_data)) {
                        $email = $validated_form->validated("email");
                        $login = null;
                        if ($with_login) {
                            $login = strtolower($validated_form->validated("login"));
                            if ($email == "") {
                                $domain = $this->whitelabel['domain'];
                                $email = 'anonymous+' . $login . '@' . $domain;
                            }
                        }
                        if ($unique_emails) {
                            if ($this->check_email_used($this->whitelabel, $email)) {
                                echo 'This e-mail is already registered.';
                                echo '<br>';
                                throw new Exception(
                                    'Data not saved - failed for line no. ' .
                                    $counter . ', login: ' .
                                    $user_data['login'] . ', password: ' .
                                    $user_data['password'] . ', email: ' .
                                    $user_data['email']
                                );
                            }
                        }
                        list(
                            $token,
                            $salt,
                            $hash
                        ) = $this->generate_token_salt_hash($this->whitelabel['id'], $validated_form->validated("password"));

                        $newuser = Model_Whitelabel_User::forge();
                        $newuser->set([
                            'token' => $token,
                            'whitelabel_id' => $this->whitelabel['id'],
                            'language_id' => 1,
                            'currency_id' => $this->whitelabel['currency_id'],
                            'is_active' => 1,
                            'is_confirmed' => 1,
                            'email' => $email,
                            'login' => $login,
                            'hash' => $hash,
                            'salt' => $salt,
                            'name' => '',
                            'surname' => '',
                            'address_1' => '',
                            'address_2' => '',
                            'city' => '',
                            'country' => '',
                            'state' => '',
                            'zip' => '',
                            'phone_country' => '',
                            'gender' => Model_Whitelabel_User::GENDER_UNSET,
                            'national_id' => '',
                            'birthdate' => null,
                            'phone' => '',
                            'timezone' => '',
                            'date_register' => DB::expr("NOW()"),
                            'balance' => 0,
                            'register_ip' => Lotto_Security::get_IP(),
                            'last_ip' => Lotto_Security::get_IP(),
                            'last_active' => DB::expr("NOW()"),
                            'last_update' => DB::expr("NOW()"),
                            'last_country' => null,
                            'register_country' => null,
                            'referrer_id' => null,
                            'connected_aff_id' => null
                        ]);
                        $newuser->save();
                        $counter++;
                    } else {
                        $errors = Lotto_Helper::generate_errors($this->validation->error());
                        foreach ($errors as $error) {
                            echo $error . '<br>';
                        }
                        throw new Exception(
                            'Data not saved - failed for line no. ' .
                            $counter . ', login: ' .
                            $user_data['login'] . ', password: ' .
                            $user_data['password'] . ', email: ' .
                            $user_data['email']
                        );
                    }
                }
            }
           
            DB::commit_transaction();
        } catch (\Throwable $e) {
            DB::rollback_transaction();
            $fileLoggerService->error(
                $e->getMessage()
            );
            return $e->getMessage();
        }

        return "Data saved.";
    }
}
