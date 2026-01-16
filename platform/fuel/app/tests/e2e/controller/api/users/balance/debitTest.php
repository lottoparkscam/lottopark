<?php


use Wrappers\Db;
use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelUserBalanceLog;

class Tests_E2e_Controller_Api_Users_Balance_Debit extends Test_E2e_Controller_Api
{
    private string $method;

    private string $endpoint;

    private string $email;

    private WhitelabelUser $user;

    private Db $db;

    private Currency $usdCurrency;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = Container::get(Db::class);

        $this->usdCurrency = Currency::find('first', [
            'where' => [
                'code' => 'USD'
            ]
        ]);

        $this->whitelabel->set([
            'max_daily_balance_change_per_user' => 101,
            'user_balance_change_limit' => 200,
            'is_reducing_balance_increases_limits' => true,
            'use_logins_for_users' => false,
            'currency_id' => $this->usdCurrency->id,
            'is_balance_change_global_limit_enabled_in_api' => true
        ]);
        $this->whitelabel->currency = $this->usdCurrency;
        $this->whitelabel->save();

        $this->method = "PATCH";
        $this->endpoint = "/api/users/balance/debit";
        $this->email = 'test@user.loc';

        $this->user = WhitelabelUser::find('first', [
            'where' => [
                'email' => $this->email,
            ]
        ]);
        $this->user->currency = $this->usdCurrency;
        $this->user->set([
            'balance' => 100,
            'is_deleted' => false,
            'login' => null,
            'currency_id' => $this->usdCurrency->id
        ]);
        $this->user->save();

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $this->reset_balance_history();

        // add other records to history to check if it's not in daily limit
        Model_Whitelabel_User_Balance_Log::add_whitelabel_user_balance_log(
            $this->user->id,
            $now->modify('-1 day')->format('Y-m-d H:i:s'),
            'Balance updated.',
            0,
            false,
            100,
            'USD',
            0,
            null
        );

        Model_Whitelabel_User_Balance_Log::add_whitelabel_user_balance_log(
            $this->user->id,
            $now->modify('+2 day')->format('Y-m-d H:i:s'),
            'Balance updated.',
            0,
            false,
            150,
            'USD',
            0,
            null
        );

        $this->refresh_data();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        /** @var Currency $eurCurrency */
        $eurCurrency = Currency::find('first', [
            'where' => [
                'code' => 'EUR'
            ]
        ]);
        $this->whitelabel->set([
            'currency_id' => $eurCurrency->id,
            'use_logins_for_users' => false
        ]);
        $this->whitelabel->currency = $eurCurrency;
        $this->whitelabel->save();
        $this->reset_balance_history();
    }

    /** @test  */
    public function reduce_balance_via_email()
    {
        // amount send via api => should it work
        $set_of_critical_amounts_block_by_available_user_balance = [
            -5 => false,
            0 => false,
            1 => true,
            99 => true,
            2 => false, // because is bigger than user had
        ];

        $this->change_balance_main_cases($set_of_critical_amounts_block_by_available_user_balance, $this->email);
        $this->reset_balance_history();

        // check same scenario without increasing limits
        $this->whitelabel->set([
            'is_reducing_balance_increases_limits' => false
        ]);
        $this->whitelabel->save();

        $this->user->set([
            'balance' => 100
        ]);
        $this->user->save();

        $this->change_balance_main_cases($set_of_critical_amounts_block_by_available_user_balance, $this->email);


        // check other scenario
        // where is_balance_change_global_limit_enabled_in_api is false
        // amount send via api => should it work
        $set_of_critical_amounts_block_by_available_user_balance = [
            0 => false,
            99 => true,
        ];

        $this->user->set([
            'balance' => 100
        ]);
        $this->user->save();

        $this->whitelabel->set([
            'is_reducing_balance_increases_limits' => true,
            'is_balance_change_global_limit_enabled_in_api' => false
        ]);
        $this->whitelabel->save();

        $this->change_balance_main_cases($set_of_critical_amounts_block_by_available_user_balance, $this->email);
    }

    /** @test  */
    public function reduce_balance_via_incorrect_email()
    {
        $email = 'testasdasdqweqwe@user.loc';

        $response = $this->send_request($email, 100);

        $expected_response = [
            'status' => 'error'
        ];

        $this->assertSame($expected_response['status'], $response['status']);
    }

    /** @test  */
    public function reduce_balance_via_email_if_user_is_deleted()
    {
        $email = 'test@user.loc';
        $this->set_user_as_deleted_by_email($email);

        $response = $this->send_request($email, 100);

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => ['User does not exist'],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_login_if_email_login_only_enabled()
    {
        $login = 'tojestlogindotestow';

        $response = $this->send_request('', 100, $login);

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => [
                        'user_email' => 'Field user_email is required'
                    ],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_login()
    {
        // amount send via api => should it work
        $set_of_critical_amounts_block_by_available_user_balance = [
            -5 => false,
            0 => false,
            1 => true,
            99 => true,
            2 => false, // because is bigger than user had
        ];

        $login = 'tojestlogindotestow';
        $this->set_logins_for_users_for_whitelabel($login);
        $this->change_balance_main_cases($set_of_critical_amounts_block_by_available_user_balance, '', $login);
        $this->reset_balance_history();

        // check same scenario without incresing limits
        $this->whitelabel->set([
            'is_reducing_balance_increases_limits' => false
        ]);
        $this->whitelabel->save();

        $this->user->set([
            'balance' => 100
        ]);
        $this->user->save();

        $this->change_balance_main_cases($set_of_critical_amounts_block_by_available_user_balance, '', $login);
    }

    /** @test  */
    public function reduce_balance_via_incorrect_login()
    {
        $login = 'tojestlogindotestow';
        $this->set_logins_for_users_for_whitelabel('tojestlogindotestow');

        $response = $this->send_request('', 100, $login . "costutajdopisane");

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => ['User does not exist'],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_login_if_user_is_deleted()
    {
        $login = 'tojestlogindotestow';
        $this->set_logins_for_users_for_whitelabel('tojestlogindotestow');
        $this->set_user_as_deleted_by_login($login);

        $response = $this->send_request('', 100, "{$login}costutajdopisane");

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => ['User does not exist'],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_email_if_login_by_login_only_enabled()
    {
        $this->set_logins_for_users_for_whitelabel('samelogin');

        $email = 'test@user.loc';

        $response = $this->send_request($email, 100);

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => [
                        'user_login' => 'Field user_login is required'
                    ],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_email_if_currency_code_not_exists()
    {
        $email = 'test@user.loc';

        $response = $this->send_request($email, 100, '', 'A');

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => [
                        'currency_code' => 'The field Currency Code must contain exactly 3 characters.'
                    ],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    /** @test  */
    public function reduce_balance_via_email_if_currency_code_is_other_than_users()
    {
        $email = 'test@user.loc';
        $currencyCode = 'PLN';

        /** @var Currency $usdCurrency */
        $usdCurrency = Currency::find('first', [
            'where' => [
                'code' => 'USD'
            ]
        ]);

        $this->user->set([
            'currency_id' => $usdCurrency->id
        ]);
        $this->user->save();
        $previousUserBalance = $this->user->balance;
        $amount = 10;
        $amountInUsd = (float)Helpers_Currency::convert_to_any(
            $amount,
            $currencyCode,
            'USD'
        );

        $date_before_request = new DateTime('now', new DateTimeZone('UTC'));
        $this->send_request($email, $amount, '', $currencyCode);
        $date_after_request = new DateTime('now', new DateTimeZone('UTC'));
        $this->refresh_data();
        $newBalance = $this->user->balance;

        $this->assertSame($previousUserBalance - $amountInUsd, $newBalance);

        // check if record is in history
        $records_in_history = Model_Whitelabel_User_Balance_Log::find([
                'where' => [
                    'whitelabel_user_id' => $this->user->id,
                    ['session_datetime', '>=', $date_before_request->format('Y-m-d H:i:s')],
                    ['session_datetime', '<=', $date_after_request->format('Y-m-d H:i:s')],
                    'balance_change_before_conversion' => -$amount,
                    'balance_change_before_conversion_currency_code' => 'PLN'
                ]
            ]) ?? [];
        $record_in_history = end($records_in_history);

        $this->assertSame((float)-$amountInUsd, (float)$record_in_history['balance_change']);

    }

    /** @test  */
    public function reduce_balance_via_login_if_currency_code_not_exists()
    {
        $this->set_logins_for_users_for_whitelabel('samelogin');

        $email = 'test@user.loc';

        $response = $this->send_request($email, 100, 'login', 'A');

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => [
                        'currency_code' => 'The field Currency Code must contain exactly 3 characters.'
                    ],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }

    private function send_request(string $email, float $amount, string $login = '', ?string $currencyCode = null)
    {
        $body = [
            'user_email' => $email,
            'user_login' => $login,
            'amount' => $amount,
            'currency_code' => $currencyCode ?? $this->user->currency->code
        ];

        $options = [
            'form_params' => $body
        ];

        return $this->get_response_with_security_check($this->method, $this->endpoint, $options);
    }

    private function set_logins_for_users_for_whitelabel(string $login): void
    {
        $this->whitelabel->set([
            'use_logins_for_users' => true
        ]);
        $this->whitelabel->save();

        $this->user->set([
            'login' => $login
        ]);
        $this->user->save();

        $this->refresh_data();
    }

    private function set_user_as_deleted_by_email(string $email): void
    {
        $whitelabel_user = WhitelabelUser::find('first', [
            'email' => $email
        ]);
        $whitelabel_user->set(['is_deleted' => true]);
        $whitelabel_user->save();
    }

    private function set_user_as_deleted_by_login(string $login): void
    {
        $whitelabel_user = WhitelabelUser::find('first', [
            'login' => $login
        ]);
        $whitelabel_user->set(['is_deleted' => true]);
        $whitelabel_user->save();
    }

    private function refresh_data(): void
    {
        Whitelabel::flush_cache();
        WhitelabelUser::flush_cache();

        $whitelabel_user_id = $this->user->id;
        $this->user = WhitelabelUser::find($whitelabel_user_id);

        $whitelabel_id = $this->whitelabel->id;
        $this->whitelabel = Whitelabel::find($whitelabel_id);
    }

    /**
     * @param array $data_set
     * @param string $email
     * @param string $login
     *
     * Check critical amount values
     * Check if record in history balance change log has been created
     * Check if user balance has been changed
     * Check if global limits per whitelabel has been changed
     */
    private function change_balance_main_cases(array $data_set, string $email = '', string $login = ''): void
    {
        foreach ($data_set as $amount => $should_work) {
            $previous_user_balance_change_limit = $this->whitelabel->user_balance_change_limit;
            $previous_user_balance = $this->user->balance;
            $date_before_request = new DateTime('now', new DateTimeZone('UTC'));
            $sql_date_before_request = $this->db->query('SELECT NOW() AS NOW', null)->execute()[0]['NOW'];
            $response = $this->send_request($email, $amount, $login);
            $date_after_request = new DateTime('now', new DateTimeZone('UTC'));
            $sql_date_after_request = $this->db->query('SELECT NOW() AS NOW', null)->execute()[0]['NOW'];
            $this->refresh_data();

            if ($should_work) {
                // check request
                $expected_response = [
                    'status' => 'success',
                    'data' => [
                        'Balance has been changed'
                    ]
                ];

                $this->assertSame($expected_response, $response);

                // check if record is in history
                $records_in_history = Model_Whitelabel_User_Balance_Log::find([
                    'where' => [
                        'whitelabel_user_id' => $this->user->id,
                        ['session_datetime', '>=', $date_before_request->format('Y-m-d H:i:s')],
                        ['session_datetime', '<=', $date_after_request->format('Y-m-d H:i:s')],
                        'balance_change_before_conversion' => -$amount,
                        'balance_change_before_conversion_currency_code' => $this->user->currency->code
                    ]
                ]) ?? [];
                $record_in_history = end($records_in_history);

                $this->assertSame((float)-$amount, (float)$record_in_history['balance_change']);

                //check if user's balance has changed
                $this->assertSame($previous_user_balance - $amount, $this->user->balance);

                if ($this->whitelabel->is_balance_change_global_limit_enabled_in_api &&
                    $this->whitelabel->is_reducing_balance_increases_limits) {

                    $amountInWhitelabelCurrency = (float)Helpers_Currency::convert_to_any(
                        $amount,
                        $this->usdCurrency->code,
                        $this->whitelabel->currency->code
                    );

                    // check if global limit has been increased
                    $this->assertSame(
                        $previous_user_balance_change_limit + $amountInWhitelabelCurrency,
                        $this->whitelabel->user_balance_change_limit
                    );

                    // check if log after change global limit exists
                    $global_change_logs = Model_Whitelabel_User_Balance_Change_Limit_Log::find([
                            'where' => [
                                'whitelabel_id' => $this->whitelabel->id,
                                ['created_at', '>=', $sql_date_before_request],
                                ['created_at', '<=', $sql_date_after_request],
                            ]
                        ]) ?? [];
                    $last_global_change_log = end($global_change_logs);

                    $this->assertSame((float)$amountInWhitelabelCurrency, (float)$last_global_change_log['value']);
                } else {
                    // check if global limit has not been increased
                    $this->assertSame($previous_user_balance_change_limit, $this->whitelabel->user_balance_change_limit);
                }

            } else {
                $this->assertStringContainsStringIgnoringCase('Wrong balance amount', $response['errors']['message']['amount']);

                // check if record is not in history
                $record_in_history = Model_Whitelabel_User_Balance_Log::find([
                    'where' => [
                        'whitelabel_user_id' => $this->user->id,
                        ['created_at', '>=', $date_before_request->format('Y-m-d H:i:s')],
                        ['created_at', '<=', $date_after_request->format('Y-m-d H:i:s')],
                        'balance_change' => $amount,
                        'balance_change_before_conversion' => $amount,
                        'balance_change_before_conversion_currency_code' => $this->user->currency->code
                    ]
                ]);

                $this->assertEmpty($record_in_history);

                //check if user's balance has not changed
                $this->assertSame($previous_user_balance, $this->user->balance);

                // check if global limit has not been increased
                $this->assertSame($previous_user_balance_change_limit, $this->whitelabel->user_balance_change_limit);
            }
        }
    }

    private function reset_balance_history(): void
    {
        $balance_logs = WhitelabelUserBalanceLog::find('all', [
            'where' => [
                'whitelabel_user_id' => $this->user->id
            ]
        ]);

        if (!empty($balance_logs)) {
            /** @var Model_Whitelabel_User_Balance_Log $balance_log */
            foreach ($balance_logs as $balance_log) {
                $balance_log->delete();
            }
        }

        $change_balance_limit_logs = Model_Whitelabel_User_Balance_Change_Limit_Log::find([
            'where' => [
                'whitelabel_id' => $this->whitelabel->id
            ]
        ]);

        if (!empty($change_balance_limit_logs)) {
            /** @var Model_Whitelabel_User_Balance_Change_Limit_Log $row */
            foreach ($change_balance_limit_logs as $change_balance_limit_log) {
                $change_balance_limit_log->delete();
            }
        }

        $this->db->query("ALTER TABLE whitelabel_user_balance_change_limit_log AUTO_INCREMENT = 1", null)->execute();
        $this->db->query("ALTER TABLE whitelabel_user_balance_log AUTO_INCREMENT = 1", null)->execute();
    }
}