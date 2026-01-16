<?php

use Services\Logs\FileLoggerService;

class Forms_Wordpress_Exportalldata
{

    /**
     *
     * @var array
     */
    private $user;

    /**
     *
     * @var string
     */
    private $main_rodo_folder = "";

    /**
     *
     * @var array
     */
    private $whitelabel = [];
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var string
     */
    private $zip_file = "";

    /**
     *
     * @var string
     */
    private $user_folder = "";

    /**
     *
     * @var string
     */
    private $user_name = "";

    /**
     *
     * @var array
     */
    private $countries = [];

    /**
     *
     * @var array
     */
    private $timezones = [];

    /**
     *
     * @var array
     */
    private $currencies = [];

    /**
     *
     * @var array
     */
    private $messages = [];

    /**
     * @param array $whitelabel
     */
    public function __construct($user, $whitelabel = [])
    {
        $this->messages = [];
        $this->user = $user;
        $this->whitelabel = $whitelabel;
        $this->main_rodo_folder = APPPATH . "tmp/rodo/";
        if (!(file_exists($this->main_rodo_folder) && is_writable($this->main_rodo_folder))) {
            try {
                throw new Exception("The main folder does not exist or is not writable: " . $this->main_rodo_folder);
            } catch (Exception $e) {
                $this->message = ['error', _("Unknown error! Please contact us!")];
                $this->fileLoggerService->error($e->getMessage());
            }
        }

        $prefix = '';
        if (!empty($whitelabel['prefix'])) {
            $prefix = $whitelabel['prefix'];
        } else {
            $whitelabel_obj = Model_Whitelabel::find_by_pk($user['whitelabel_id']);
            if (!empty($whitelabel_obj) && !empty($whitelabel_obj->prefix)) {
                $prefix = $whitelabel_obj->prefix;
            }
        }

        $this->user_name = $prefix . 'U' . $user["token"];

        $this->countries = Lotto_Helper::get_localized_country_list();
        $this->timezones = Lotto_Helper::get_timezone_list();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_countries()
    {
        return $this->countries;
    }

    /**
     *
     * @return array
     */
    public function get_timezones()
    {
        return $this->timezones;
    }

    /**
     *
     * @return array
     */
    public function get_currencies()
    {
        if (empty($this->currencies)) {
            $this->currencies = Model_Currency::get_all_currencies();
        }

        return $this->currencies;
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
     * @return string
     */
    public function get_main_rodo_folder()
    {
        if (!(file_exists($this->main_rodo_folder) && is_writable($this->main_rodo_folder))) {
            try {
                $message = ['error', _("Unknown error! Please contact us!")];
                $this->set_messages($message);
                throw new Exception("The main folder does not exist or is not writable: " . $this->main_rodo_folder);
            } catch (Exception $e) {
                $this->fileLoggerService->error($e->getMessage());
            }
        }

        return $this->main_rodo_folder;
    }

    /**
     *
     * @return string
     */
    public function get_user_folder()
    {
        if (empty($this->user_folder)) {
            $main_rodo_folder = $this->get_main_rodo_folder();
            $current_date = microtime();
            $user_folder = $this->get_user_name() . md5($current_date);
            $folder = $main_rodo_folder . $user_folder;

            if (!file_exists($folder)) {
                try {
                    if (!is_writable($main_rodo_folder)) {
                        throw new Exception("The folder " . $main_rodo_folder . " is not writeble");
                    } else {
                        try {
                            $res = mkdir($folder);

                            if (!$res) {
                                throw new Exception("There is a problem to make a folder for the user. Folder: " . $folder);
                            }
                            if (!is_writable($folder)) {
                                throw new Exception("The folder for the user is not writable: " . $folder);
                            }

                            $this->user_folder = $user_folder;
                        } catch (Exception $e) {
                            $this->fileLoggerService->error($e->getMessage());
                            $message = ['error', _("Unknown error! Please contact us!")];
                            $this->set_messages($message);
                        }
                    }
                } catch (Exception $e) {
                    $this->fileLoggerService->error($e->getMessage());
                    $message = ['error', _("Unknown error! Please contact us!")];
                    $this->set_messages($message);
                }
            }
        }

        return $this->user_folder;
    }

    /**
     *
     * @return string
     */
    public function get_zip_file()
    {
        if (empty($this->zip_file)) {
            $current_date = microtime();
            $zipfile = $this->get_user_name() . md5($current_date) . ".zip";
            $this->zip_file = $zipfile;
        }

        return $this->zip_file;
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
     * @return array
     */
    public function get_user_name()
    {
        return $this->user_name;
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
     * @param array $messages
     */
    public function set_messages($messages)
    {
        $this->messages = $messages;
    }

    /**
     *
     */
    public function get_prepared_form()
    {
    }

    /**
     *
     * @return null
     */
    public function process_form()
    {
        return;
    }

    /**
     *
     * @param type $filename
     */
    private function check_file_opening($filename)
    {
        $result = [
            false,
            false
        ];
        $user_folder = $this->get_user_folder();

        try {
            // This situation should not be happend.
            // Only if there is a problem with creation of the folder
            // for user data!!!
            if (empty($user_folder)) {
                throw new Exception("There is a problem with opening the " . $user_folder . " folder!");
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
            return $result;
        }

        $user_data_folder = $this->get_main_rodo_folder() . $user_folder . "/";
        $csv_filename = $user_data_folder . $filename;

        try {
            if (!is_writable($user_data_folder)) {
                throw new Exception("The folder for the user is not writable: " . $user_data_folder);
            }
            $fp = fopen($csv_filename, 'w');
            if ($fp === false) {
                throw new Exception("There is a problem with opening the " . $csv_filename . " file!");
            }
            $result = [
                true,
                $fp
            ];
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_userdata_csv()
    {
        list($result, $fp) = $this->check_file_opening('userdata.csv');

        if (!$result) {
            return $result;
        }

        $users_data = $this->get_users_data();

        $countries = $this->get_countries();
        $timezones = $this->get_timezones();

        // Settings for CSV
        $headers = [
            _("User ID"),
            _("E-mail"),
            _("First Name"),
            _("Last Name"),
            _("Balance"),
            _("Address #1"),
            _("Address #2"),
            _("City"),
            _("Country"),
            _("Region"),
            _("Postal/ZIP Code"),
            _("Phone Country"),
            _("Phone"),
            _("Birthdate"),
            _("Time Zone"),
            _("Date register"),
            _("Register IP"),
            _("Last IP"),
            _("Register Country"),
            _("Last Country"),
            _("Last Active"),
            _("Last Update"),
            _("First Purchase"),
        ];

        fputcsv($fp, $headers);

        if ($users_data !== null && count($users_data) > 0) {
            foreach ($users_data as $user) {
                $user_id = '-';
                if (!empty($user['user_prefix_token'])) {
                    $user_id = $user['user_prefix_token'];
                }

                $email = '-';
                if (!empty($user['email'])) {
                    $email = $user['email'];
                }

                $name = _("Anonymous");
                if (!empty($user['name'])) {
                    $name = $user['name'];
                }

                $surname = _("Anonymous");
                if (!empty($user['surname'])) {
                    $surname = $user['surname'];
                }

                $balance = (!empty($user['balance']) ? $user['balance'] : 0.00);
                if (!empty($user['c_code'])) {
                    $balance_tmp = Lotto_View::format_currency($balance, $user['c_code'], true);
                    $balance = $balance_tmp;
                }

                $address1 = "-";
                if (!empty($user['address_1'])) {
                    $address1 = $user['address_1'];
                }

                $address2 = "-";
                if (!empty($user['address_2'])) {
                    $address2 = $user['address_2'];
                }

                $city = "-";
                if (!empty($user['city'])) {
                    $city = $user['city'];
                }

                $country = "-";
                if (!empty($user['country'])) {
                    $country = $user['country'];
                }

                $phone = "-";
                $phone_country = "-";
                $phone_country_add = '';
                if (!empty($user['phone_country']) && !empty($countries[$user['phone_country']])) {
                    $phone_country = $countries[$user['phone_country']];
                    $phone_country_add = ' (' . $countries[$user['phone_country']] . ')';
                }
                if (!empty($user['phone']) && !empty($user['phone_country'])) {
                    $phone = Lotto_View::format_phone($user['phone'], $user['phone_country']) . $phone_country_add;
                }

                $region = '-';
                if (!empty($user['state'])) {
                    $region = Lotto_View::get_region_name($user['state'], true, false);
                }

                $zip = "-";
                if (!empty($user['zip'])) {
                    $zip = $user['zip'];
                }

                $birthday = "-";
                if (!empty($user['birthdate'])) {
                    $birthday = Lotto_View::format_date(
                        $user['birthdate'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::NONE
                    );
                }

                $timezone = "-";
                if (!empty($user['timezone']) && isset($timezones[$user['timezone']])) {
                    $timezone = $timezones[$user['timezone']];
                }

                $date_register = Lotto_View::format_date(
                    $user['date_register'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $register_ip = "-";
                if (!empty($user['register_ip'])) {
                    $register_ip = $user['register_ip'];
                }

                $last_ip = "-";
                if (!empty($user['last_ip'])) {
                    $last_ip = $user['last_ip'];
                }

                $register_country = "-";
                if (!empty($user['register_country'])) {
                    $register_country = $user['register_country'];
                }

                $last_country = "-";
                if (!empty($user['last_country'])) {
                    $last_country = $user['last_country'];
                }

                $last_active = Lotto_View::format_date(
                    $user['last_active'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $last_update = Lotto_View::format_date(
                    $user['last_update'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $first_purchase = "-";
                if (!empty($user['first_purchase'])) {
                    $first_purchase = Lotto_View::format_date(
                        $user['first_purchase'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::SHORT
                    );
                }

                $data_to_insert = [
                    $user_id,
                    $email,
                    $name,
                    $surname,
                    $balance,
                    $address1,
                    $address2,
                    $city,
                    $country,
                    $region,
                    $zip,
                    $phone_country,
                    $phone,
                    $birthday,
                    $timezone,
                    $date_register,
                    $register_ip,
                    $last_ip,
                    $register_country,
                    $last_country,
                    $last_active,
                    $last_update,
                    $first_purchase
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_transactions_csv()
    {
        list($result, $fp) = $this->check_file_opening('transactions.csv');

        if (!$result) {
            return $result;
        }

        $transactions_data = $this->get_transactions_data();

        $headers = [
            _("Transaction ID"),
            _("Payment Type"),
            _("Amount"),
            _("Date"),
            _("Date Confirmed"),
            _("Status"),
        ];

        fputcsv($fp, $headers);

        if ($transactions_data !== null && count($transactions_data) > 0) {
            foreach ($transactions_data as $transaction) {
                $transactionID = "-";
                if (!empty($transaction['trans_prefix_token'])) {
                    $transactionID = $transaction['trans_prefix_token'];
                }

                $payment_type = "-";
                if (!empty($transaction['method_name'])) {
                    $payment_type = $transaction['method_name'];
                }
                
                $amount = (!empty($transaction['amount']) ? $transaction['amount'] : 0.00);
                if (!empty($transaction['c_code'])) {
                    $amount_tmp = Lotto_View::format_currency($amount, $transaction['c_code'], true);
                    $amount = $amount_tmp;
                }

                $date = Lotto_View::format_date(
                    $transaction['date'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $date_confirmed = Lotto_View::format_date(
                    $transaction['date_confirmed'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );

                $status = "-";
                switch ($transaction['status']) {
                    case Helpers_General::STATUS_TRANSACTION_PENDING:
                        $status = _("Pending");
                        break;
                    case Helpers_General::STATUS_TRANSACTION_APPROVED:
                        $status = _("Approved");
                        break;
                    case Helpers_General::STATUS_TRANSACTION_ERROR:
                        $status = _("Error");
                        break;
                }

                $data_to_insert = [
                    $transactionID,
                    $payment_type,
                    $amount,
                    $date,
                    $date_confirmed,
                    $status
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_tickets_csv()
    {
        list($result, $fp) = $this->check_file_opening('tickets.csv');

        if (!$result) {
            return $result;
        }

        $tickets_data = $this->get_tickets_data();

        $headers = [
            _("Ticket ID"),
            _("Transaction ID"),
            _("Lottery"),
            _("Draw Date"),
            _("Amount"),
            _("Date"),
            _("Date Processed"),
            _("Status"),
            _("Paid"),
            _("Prize"),
            _("Prize Net"),
            _("Prize Jackpot"),
            _("Prize Quickpick"),
        ];

        fputcsv($fp, $headers);

        if ($tickets_data !== null && count($tickets_data) > 0) {
            foreach ($tickets_data as $ticket) {
                $ticketID = "-";
                if (!empty($ticket['ticket_prefix_token'])) {
                    $ticketID = $ticket['ticket_prefix_token'];
                }

                $transactionID = "-";
                if (!empty($ticket['trans_prefix_token'])) {
                    $transactionID = $ticket['trans_prefix_token'];
                }

                $lottery_name = "-";
                if (!empty($ticket['lottery_name'])) {
                    $lottery_name = $ticket['lottery_name'];
                }

                $draw_date = '-';
                if (!empty($ticket['draw_date'])) {
                    $draw_date = Lotto_View::format_date(
                        $ticket['draw_date'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::NONE
                    );
                }

                $amount = (!empty($ticket['amount']) ? $ticket['amount'] : 0.00);
                if (!empty($ticket['c_code'])) {
                    $amount_tmp = Lotto_View::format_currency($amount, $ticket['c_code'], true);
                    $amount = $amount_tmp;
                }

                $date = '-';
                if (!empty($ticket['date'])) {
                    $date = Lotto_View::format_date(
                        $ticket['date'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::SHORT
                    );
                }

                $date_processed = '-';
                if (!empty($ticket['date_processed'])) {
                    $date_processed = Lotto_View::format_date(
                        $ticket['date_processed'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::SHORT
                    );
                }

                $status = '-';
                switch ($ticket['status']) {
                    case Helpers_General::TICKET_STATUS_PENDING:
                        $status = _("Pending");
                        break;
                    case Helpers_General::TICKET_STATUS_WIN:
                        $status = _("Win");
                        break;
                    case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                        $status = _("No winnings");
                        break;
                    case Helpers_General::TICKET_STATUS_QUICK_PICK:
                        $status = _("Quick Pick");

                        break;
                    case Helpers_General::TICKET_STATUS_CANCELED:
                        $status = _("Cancelled");
                        break;
                }

                $paid_text = '-';
                if ($ticket['paid'] == Helpers_General::TICKET_PAID) {
                    $paid_text = _('Yes');
                } elseif ($ticket['paid'] == Helpers_General::TICKET_UNPAID) {
                    $paid_text = _('No');
                }

                $prize = (!empty($ticket['prize']) ? $ticket['prize'] : 0.00);
                if (!empty($ticket['c_code'])) {
                    $prize_tmp = Lotto_View::format_currency($prize, $ticket['c_code'], true);
                    $prize = $prize_tmp;
                }

                $prize_net = (!empty($ticket['prize_net']) ? $ticket['prize_net'] : 0.00);
                if (!empty($ticket['c_code'])) {
                    $prize_net_tmp = Lotto_View::format_currency($prize_net, $ticket['c_code'], true);
                    $prize_net = $prize_net_tmp;
                }

                $prize_jackpot = '';
                if ($ticket['prize_jackpot'] > 0) {
                    $prize_jackpot = _("Jackpot");
                    if ($ticket['prize_net'] > 0) {
                        $prize_jackpot .= " + ";
                    }
                }

                $prize_quickpick = '';
                if ($ticket['prize_quickpick'] > 0) {
                    if ($ticket['prize_net'] > 0 ||
                        (!empty($ticket['prize_jackpot']) && $ticket['prize_jackpot'] > 0)
                    ) {
                        $prize_quickpick = " + ";
                    }
                    $prize_quickpick .= $ticket['prize_quickpick'] . '*' . _("Quick Pick");
                }

                $data_to_insert = [
                    $ticketID,
                    $transactionID,
                    $lottery_name,
                    $draw_date,
                    $amount,
                    $date,
                    $date_processed,
                    $status,
                    $paid_text,
                    $prize,
                    $prize_net,
                    $prize_jackpot,
                    $prize_quickpick
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_tickets_lines_csv()
    {
        list($result, $fp) = $this->check_file_opening('lines.csv');

        if (!$result) {
            return $result;
        }

        $lines_data = $this->get_tickets_lines_data();

        $headers = [
            _("Ticket ID"),
            _("Numbers"),
            _("Bonus Numbers"),
            _("Amount"),
            _("Status"),
            _("Prize"),
        ];

        fputcsv($fp, $headers);

        if ($lines_data !== null && count($lines_data) > 0) {
            foreach ($lines_data as $line) {
                $ticketID = "-";
                if (!empty($line['ticket_prefix_token'])) {
                    $ticketID = $line['ticket_prefix_token'];
                }

                $numbers = "-";
                if (!empty($line['numbers'])) {
                    $numbers = $line['numbers'];
                }

                $bnumbers = "-";
                if (!empty($line['bnumbers'])) {
                    $bnumbers = $line['bnumbers'];
                }

                $amount = (!empty($line['amount']) ? $line['amount'] : 0.00);
                if (!empty($line['c_code'])) {
                    $amount_tmp = Lotto_View::format_currency($amount, $line['c_code'], true);
                    $amount = $amount_tmp;
                }

                $status = '-';
                switch ($line['status']) {
                    case Helpers_General::TICKET_STATUS_PENDING:
                        $status = _("Pending");
                        break;
                    case Helpers_General::TICKET_STATUS_WIN:
                        $status = _("Win");
                        break;
                    case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                        $status = _("No winnings");
                        break;
                    case Helpers_General::TICKET_STATUS_QUICK_PICK:
                        $status = _("Quick Pick");

                        break;
                }

                $prize = (!empty($line['prize']) ? $line['prize'] : 0.00);
                if (!empty($line['c_code'])) {
                    $prize_tmp = Lotto_View::format_currency($prize, $line['c_code'], true);
                    $prize = $prize_tmp;
                }

                $data_to_insert = [
                    $ticketID,
                    $numbers,
                    $bnumbers,
                    $amount,
                    $status,
                    $prize
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_withdrawals_csv()
    {
        list($result, $fp) = $this->check_file_opening('withdrawals.csv');

        if (!$result) {
            return $result;
        }

        $withdrawals_data = $this->get_withdrawals_data();

        $headers = [
            _("Withdrawal ID"),
            _("Withdrawal Type"),
            _("Amount"),
            _("Date"),
            _("Date Confirmed"),
            _("Status"),
            _("Data"),
        ];

        fputcsv($fp, $headers);

        if ($withdrawals_data !== null && count($withdrawals_data) > 0) {
            foreach ($withdrawals_data as $withdrawal) {
                $withdrawalID = '-';
                if (!empty($withdrawal['withdrawal_prefix_token'])) {
                    $withdrawalID = $withdrawal['withdrawal_prefix_token'];
                }

                $withdrawal_type = '-';
                if (!empty($withdrawal['withdrawal_type'])) {
                    $withdrawal_type = $withdrawal['withdrawal_type'];
                }

                $amount = (!empty($withdrawal['amount']) ? $withdrawal['amount'] : 0.00);
                if (!empty($withdrawal['c_code'])) {
                    $amount_tmp = Lotto_View::format_currency($amount, $withdrawal['c_code'], true);
                    $amount = $amount_tmp;
                }

                $date = '-';
                if (!empty($withdrawal['date'])) {
                    $date = Lotto_View::format_date(
                        $withdrawal['date'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::SHORT
                    );
                }

                $date_confirmed = '-';
                if (!empty($withdrawal['date_confirmed'])) {
                    $date_confirmed = Lotto_View::format_date(
                        $withdrawal['date_confirmed'],
                        IntlDateFormatter::MEDIUM,
                        IntlDateFormatter::SHORT
                    );
                }

                $status = "-";
                switch ($withdrawal['status']) {
                    case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING:
                        $status = _("Pending");
                        break;
                    case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED:
                        $status = _("Approved");
                        break;
                    case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED:
                        $status = _("Declined");
                        break;
                    case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED:
                        $status = _("Canceled");
                        break;
                }

                $data = '-';
                if (!empty($withdrawal['data'])) {
                    $data_unserialize = unserialize($withdrawal['data']);
                    
                    $data = "";
                    $i = 1;
                    foreach ($data_unserialize as $key => $value) {
                        $data .= $key . ": " . $value;
                        if ($i < count($data_unserialize)) {
                            $data .= ", ";
                        }
                        $i++;
                    }
                }

                $data_to_insert = [
                    $withdrawalID,
                    $withdrawal_type,
                    $amount,
                    $date,
                    $date_confirmed,
                    $status,
                    $data
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return bool
     */
    private function prepare_creditcards_csv()
    {
        list($result, $fp) = $this->check_file_opening('creditcards.csv');

        if (!$result) {
            return $result;
        }

        $creditcards_data = $this->get_creditcards_data();

        $headers = [
            _("Type"),
            _("Card Number"),
            _("Exp Month"),
            _("Exp Year"),
            _("Deleted"),
            _("Last used"),
        ];

        fputcsv($fp, $headers);

        if ($creditcards_data !== null && count($creditcards_data) > 0) {
            foreach ($creditcards_data as $creditcard) {
                $type = '-';
                if (!empty($creditcard['type'])) {
                    $type = $creditcard['type'];
                }

                $card_number = '-';
                if (!empty($creditcard['card_number'])) {
                    $card_number = $creditcard['card_number'];
                }

                $exp_month = '-';
                if (!empty($creditcard['exp_month'])) {
                    $exp_month = $creditcard['exp_month'];
                }

                $exp_year = '-';
                if (!empty($creditcard['exp_year'])) {
                    $exp_year = $creditcard['exp_year'];
                }

                $deleted = _('No');
                if (!empty($creditcard['is_deleted']) && $creditcard['is_deleted'] == 1) {
                    $deleted = _('Yes');
                }

                $lastused = _('No');
                if (!empty($creditcard['is_lastused']) && $creditcard['is_lastused'] == 1) {
                    $lastused = _('Yes');
                }

                $data_to_insert = [
                    $type,
                    $card_number,
                    $exp_month,
                    $exp_year,
                    $deleted,
                    $lastused
                ];

                fputcsv($fp, $data_to_insert);
            }
        }

        fclose($fp);
        $result = true;

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_users_data()
    {
        $result = [];
        $user = $this->get_user();

        $users_data = Model_Whitelabel_User::get_full_data_for_user_rodo($user);

        if (!empty($users_data) && !empty($users_data[0])) {
            $result = $users_data;
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_transactions_data()
    {
        $result = [];
        $user = $this->get_user();

        $transactions_data = Model_Whitelabel_Transaction::get_full_data_for_user_rodo($user);

        if (!empty($transactions_data)) {
            $result = $transactions_data;
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_tickets_data()
    {
        $result = [];
        $user = $this->get_user();

        $tickets_data = Model_Whitelabel_User_Ticket::get_full_data_for_user_rodo($user);

        if (!empty($tickets_data)) {
            $result = $tickets_data;
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_tickets_lines_data()
    {
        $result = [];
        $user = $this->get_user();

        $tickets_lines_data = Model_Whitelabel_User_Ticket_Line::get_full_data_for_user_rodo($user);

        if (!empty($tickets_lines_data)) {
            $result = $tickets_lines_data;
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_withdrawals_data()
    {
        $result = [];
        $user = $this->get_user();

        $withdrawals_data = Model_Withdrawal_Request::get_full_data_for_user_rodo($user);

        if (!empty($withdrawals_data)) {
            $result = $withdrawals_data;
        }

        return $result;
    }

    /**
     *
     * @return array
     */
    private function get_creditcards_data()
    {
        $result = [];
        $user = $this->get_user();

        $creditcards_data = Model_Emerchantpay_User_CC::get_full_data_for_user_rodo($user);

        if (!empty($creditcards_data)) {
            $result = $creditcards_data;
        }

        return $result;
    }

    /**
     *
     * @return bool
     * @throws Exception
     */
    public function prepare_files_for_zip()
    {
        $result = false;
        $zip = new ZipArchive();
        $user_folder = $this->get_user_folder();

        // This situation should not be happend.
        // Only if there is a problem with creation of the folder
        // for user data!!!
        if (empty($user_folder)) {
            return $result;
        }

        $zipfolder = $this->get_main_rodo_folder() . $user_folder . "/";
        $zipfile = $this->get_zip_file();
        $archive_filename = $zipfolder . $zipfile;

        // Userdata
        $res_userdata = $this->prepare_userdata_csv();
        try {
            if (!$res_userdata) {
                throw new Exception("There is a problem with creation of the userdata.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        // Transactions
        $res_transactions = $this->prepare_transactions_csv();
        try {
            if (!$res_transactions) {
                throw new Exception("There is a problem with creation of the transactions.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        // Tickets
        $res_tickets = $this->prepare_tickets_csv();
        try {
            if (!$res_tickets) {
                throw new Exception("There is a problem with creation of the tickets.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        // Tickets
        $res_tickets_lines = $this->prepare_tickets_lines_csv();
        try {
            if (!$res_tickets_lines) {
                throw new Exception("There is a problem with creation of the lines.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        // Withdrawals
        $res_withdrawals = $this->prepare_withdrawals_csv();
        try {
            if (!$res_withdrawals) {
                throw new Exception("There is a problem with creation of the withdrawals.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        // Creditcards
        $res_creditcards = $this->prepare_creditcards_csv();
        try {
            if (!$res_creditcards) {
                throw new Exception("There is a problem with creation of the creditcards.csv file for the user!"); // Potencial problem
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        try {
            if ($zip->open($archive_filename, ZipArchive::CREATE) !== true) {
                throw new Exception("There is a problem with creation of the zip file for the user!");
            } else {
                $list_of_files = scandir($zipfolder);

                foreach ($list_of_files as $single_file) {
                    if (in_array($single_file, [".", ".."])) {
                        continue;
                    }

                    $zip->addFile($zipfolder . $single_file, $single_file);
                }

                $zip->close();

                $result = true;
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error($e->getMessage());
            $message = ['error', _("Unknown error! Please contact us!")];
            $this->set_messages($message);
        }

        return $result;
    }

    /**
     *
     * @return int
     * @throws Exception
     */
    public function get_zipped_file()
    {
        $user_folder = $this->get_user_folder();
        // This situation should not be happend.
        // Only if there is a problem with creation of the folder
        // for user data!!!
        if (empty($user_folder)) {
            return -1;
        }

        $zipfolder = $this->get_main_rodo_folder() . $user_folder . "/";
        $zipfile = $this->get_zip_file();
        $zipfilename_for_user = _("PersonalData") . ".zip";

        $archive_filename = $zipfolder . $zipfile;

        // clean the output buffer
        ob_clean();

        //send the zip file to browser
        if (file_exists($archive_filename)) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=$zipfilename_for_user");
            header("Content-Length: " . filesize($archive_filename));

            flush();
            readfile($archive_filename);

            // Delete zip file
            unlink($archive_filename);

            // Delete all csv files from folder
            $list_of_files = scandir($zipfolder);
            foreach ($list_of_files as $single_file) {
                if (in_array($single_file, [".", ".."])) {
                    continue;
                }
                unlink($zipfolder . $single_file);
            }

            // Finally remove folder
            rmdir($zipfolder);
        }
    }
}
