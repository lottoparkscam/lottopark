<?php

/**
 * File reader for manual user balance update.
 */
final class Services_User_Balance_Saver
{
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
     * @var string $date
     */
    private $date = null;

    /**
     *
     * @var bool $filter_invalid
     */
    private $filter_invalid = false;

    /**
     *
     * @var bool $is_bonus
     */
    private $is_bonus = false;

    /**
     *
     * @var bool $use_emails
     */
    private $use_emails = false;

    /**
     *
     * @var array
     */
    private $invalid_data = [];

    public function __construct(array $data, array $whitelabel, string $date, bool $filter_invalid, bool $is_bonus, bool $use_emails)
    {
        $this->data = $data;
        $this->whitelabel = $whitelabel;
        $this->date = $date;
        $this->filter_invalid = $filter_invalid;
        $this->is_bonus = $is_bonus;
        $this->use_emails = $use_emails;
    }

    private function filter_invalid_data(array $update_data, string $error_message)
    {
        if ($this->filter_invalid) {
            $already_noticed_index = array_search($update_data['no'], array_column($this->invalid_data, 'no'));
            if ($already_noticed_index > -1) {
                array_push($this->invalid_data[$already_noticed_index], $error_message);
            } else {
                array_push($update_data, $error_message);
                array_push($this->invalid_data, $update_data);
            }
        } else {
            throw new Exception($error_message);
        }
    }

    public function save_data()
    {
        try {
            DB::start_transaction();

            $limit = 100;
            $whitelabel_change_limit = (float)$this->whitelabel['user_balance_change_limit'];
            $no_limit = false;
            if ($this->whitelabel['type'] === '2' || $this->is_bonus) {
                $no_limit = true;
            }

            $batched_array = array_chunk($this->data, $limit);
            foreach ($batched_array as $batch) {
                foreach ($batch as $update_data) {
                    if ($update_data['user'] === "" && $update_data['amount'] === "" && $update_data['currency_code'] === "") {
                        continue;
                    }
                    $errors_count = 0;

                    $currency_obj = Model_Currency::find_by(['code' => $update_data['currency_code']]);
                    
                    if (empty($currency_obj[0])) {
                        $error_message = "Wrong currency code!";
                        $this->filter_invalid_data($update_data, $error_message);
                        $errors_count++;
                    }
            
                    $user_data = $update_data['user'];

                    $user_obj = [];
                    if ($this->use_emails) {
                        $user_obj = Model_Whitelabel_User::find_by(['email' => $user_data, 'whitelabel_id' => $this->whitelabel['id']]);
                    } else {
                        $user_obj = Model_Whitelabel_User::find_by(['login' => $user_data, 'whitelabel_id' => $this->whitelabel['id']]);
                    }
                    
                    if (empty($user_obj[0])) {
                        $error_message = "Wrong user: " . $user_data;
                        $this->filter_invalid_data($update_data, $error_message);
                        $errors_count++;
                    }

                    $balance_change_import = $update_data['amount'];

                    if (!is_numeric($balance_change_import)) {
                        $error_message = "Wrong amount: " . $update_data['amount'];
                        $this->filter_invalid_data($update_data, $error_message);
                        $errors_count++;
                    }

                    $balance_change_import = (float)$balance_change_import;

                    if ($balance_change_import < 0) {
                        $error_message = "Amount can't be negative! Found: " . $update_data['amount'];
                        $this->filter_invalid_data($update_data, $error_message);
                        $errors_count++;
                    }

                    if ($errors_count > 0) {
                        continue;
                    }
                    
                    $balance_change_import_currency = $currency_obj[0]->to_array();
                    $user = $user_obj[0];

                    $balance_change_import = (float)$balance_change_import;

                    $user_currency_tab = Helpers_Currency::get_mtab_currency(false, null, $user->currency_id);
                    $manager_currency_tab = Helpers_Currency::get_mtab_currency(false, null, $this->whitelabel['manager_site_currency_id']);

                    $amount_user = (float)Helpers_Currency::get_recalculated_to_given_currency(
                        $balance_change_import,
                        $balance_change_import_currency,
                        $user_currency_tab['code']
                    );

                    $amount_manager = (float)Helpers_Currency::get_recalculated_to_given_currency(
                        $balance_change_import,
                        $balance_change_import_currency,
                        $manager_currency_tab['code']
                    );
                    
                    if (($whitelabel_change_limit >= $amount_manager) || $no_limit) {
                        if (!$no_limit) {
                            $whitelabel_change_limit = $whitelabel_change_limit - $amount_manager;
                        }
                        $changed = Model_Whitelabel_User::update_balance_by_login($user_data, $amount_user, $this->is_bonus, $this->use_emails);

                        if ($changed === 0) {
                            throw new Exception("Balance update failed for item " . $update_data['no'] . ", user: " . $user_data);
                        }

                        Model_Whitelabel_User_Balance_Log::add_whitelabel_user_balance_log(
                            $user->id,
                            $this->date,
                            'Balance updated.',
                            0,
                            $this->is_bonus,
                            $amount_user,
                            $user_currency_tab['code'],
                            $balance_change_import,
                            $balance_change_import_currency['code']
                        );
                    } else {
                        $error_message = "Balance update failed - whitelabel limit is too low.";
                        $this->filter_invalid_data($update_data, $error_message);
                    }
                }
            }
            Model_Whitelabel::update_balance_limit($this->whitelabel['id'], $whitelabel_change_limit);
            $change = $whitelabel_change_limit - (float)$this->whitelabel['user_balance_change_limit'];
            Model_Whitelabel_User_Balance_Change_Limit_Log::add_log($this->whitelabel['id'], $change);

            DB::commit_transaction();
        } catch (\Throwable $e) {
            DB::rollback_transaction();
            DB::query("ALTER TABLE whitelabel_user_balance_log AUTO_INCREMENT = 0")->execute();
            return $e->getMessage();
        }

        if (count($this->invalid_data) > 0) {
            return $this->invalid_data;
        }

        return "Data saved.";
    }
}
