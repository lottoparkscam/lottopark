<?php

/**
 * Description of Forms_Whitelabel_Withdrawal_View
 */
final class Forms_Whitelabel_Withdrawal_View extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var string|null
     */
    private $token = null;
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @param string $token
     * @param array $whitelabel
     */
    public function __construct(string $token = null, array $whitelabel = [])
    {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
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
     * @return \View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @param array $withdrawal
     * @return array
     */
    private function prepare_withdrawal_data_to_show($withdrawal): array
    {
        $whitelabel = $this->get_whitelabel();

        $user = Model_Whitelabel_User::get_user_with_currencies_by_id_and_whitelabel(
            $withdrawal['whitelabel_user_id'],
            $whitelabel
        );
        
        //$currencies = Lotto_Settings::getInstance()->get("currencies");
            
        $withdrawal_data = [];

        $full_token = '-';
        if (!empty($withdrawal['token'])) {
            $full_token = $whitelabel['prefix'] . 'W' . $withdrawal['token'];
        }
        $withdrawal_data['full_token'] = Security::htmlentities($full_token);

        $user_full_token = '-';
        if (!empty($user['token'])) {
            $user_full_token = $whitelabel['prefix'] . 'U' . $user['token'];
        }
        $withdrawal_data['user_full_token'] = Security::htmlentities($user_full_token);

        $first_name = _("Anonymous");
        if (!empty($user['name'])) {
            $first_name = $user['name'];
        }
        $withdrawal_data['first_name'] = Security::htmlentities($first_name);

        $surname = _("Anonymous");
        if (!empty($user['surname'])) {
            $surname = $user['surname'];
        }
        $withdrawal_data['surname'] = Security::htmlentities($surname);

        $withdrawal_data['email'] = Security::htmlentities($user['email']);

        $withdrawal_data['date'] = Lotto_View::format_date(
            $withdrawal['date'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        $withdrawal_data['date_confirmed'] = Lotto_View::format_date(
            $withdrawal['date_confirmed'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        $withdrawal_data['method_name'] = _($withdrawal['name']);

        $withdrawal_data['amount_manager'] = Lotto_View::format_currency(
            $withdrawal['amount_manager'],
            $withdrawal['manager_currency_code'],
            true
        );
        
        $withdrawal_data['user_amount_show'] = false;
        if ($withdrawal['withdrawal_currency_code'] !== $withdrawal['manager_currency_code']) {
            $withdrawal_data['user_amount_show'] = true;
            $user_amount_text = Lotto_View::format_currency(
                $withdrawal['amount'],
                $withdrawal['withdrawal_currency_code'],
                true
            );
            $withdrawal_data['user_amount'] = _("User currency") .
            ": " . $user_amount_text;
        }
        
        $balance_currency_tab = [
            'id' => $user['user_currency_id'],
            'code' => $user['user_currency_code'],
            'rate' => $user['user_currency_rate'],
        ];
        $balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
            $user['balance'],
            $balance_currency_tab,
            $user['manager_currency_code']
        );
        $withdrawal_data['balance_in_manager'] = Lotto_View::format_currency(
            $balance_in_manager_curr,
            $user['manager_currency_code'],
            true
        );
        
        $withdrawal_data['user_balance_show'] = false;
        if ($user['user_currency_code'] !== $user['manager_currency_code']) {
            $withdrawal_data['user_balance_show'] = true;
            $user_balance_text = Lotto_View::format_currency(
                $user['balance'],
                $user['user_currency_code'],
                true
            );
            $withdrawal_data['user_balance'] = _("User currency") .
            ": " . $user_balance_text;
        }
        
        switch ($withdrawal['status']) {
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING:
                $withdrawal_data['status_show_class'] = "";
                $withdrawal_data['status_text'] = _("Pending");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED:
                $withdrawal_data['status_show_class'] = "text-success";
                $withdrawal_data['status_text'] = _("Approved");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED:
                $withdrawal_data['status_show_class'] = "text-danger";
                $withdrawal_data['status_text'] = _("Declined");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED:
                $withdrawal_data['status_show_class'] = "text-warning";
                $withdrawal_data['status_text'] = _("Canceled");
                break;
        }
        $withdrawal_data['status'] = $withdrawal['status'];

        $data = unserialize($withdrawal['data']);

        $data_first_name = _("Anonymous");
        if (!empty($data['name'])) {
            $data_first_name = $data['name'];
        }
        $withdrawal_data['data_first_name'] = Security::htmlentities($data_first_name);

        $data_surname = _("Anonymous");
        if (!empty($data['surname'])) {
            $data_surname = $data['surname'];
        }
        $withdrawal_data['data_surname'] = Security::htmlentities($data_surname);
        
        $names_temp = [
            'address' => _("Address"),
            'neteller_email' => _("Neteller e-mail"),
            'skrill_email' => _("Skrill e-mail"),
            'bitcoin' => _("Bitcoin wallet"),
            'account_no' => _("Account IBAN"),
            'account_swift' => _("SWIFT number"),
            'bank_name' => _("Bank name"),
            'bank_address' => _("Bank address"),
            'paypal_email' => _("PayPal e-mail"),
            'fairox_account_id' => _("Fairox Account Id"),
            'usdt_wallet_type' => _("USDT Wallet Type"),
            'usdt_wallet_address' => _("USDT Wallet Address"),
            'email' => _("Email")
        ];
        foreach ($names_temp as $key => $single_name) {
            if (isset($data[$key])) {
                $withdrawal_data['data'][] = [
                    'label' => Security::htmlentities($single_name),
                    'value' => Security::htmlentities($data[$key]),
                ];
            }
        }
        
        $user_balance_class = '';
        $button_appr_classes = '';
        $button_appr_confirm_text = '';
        if ($user['balance'] < $withdrawal['amount']) {
            if ((int)$withdrawal['status'] !== Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED) {
                $user_balance_class = ' class="text-danger"';
            }
            $button_appr_classes = 'disabled ';
        } else {
            $button_appr_confirm_text = ' data-toggle="modal" ' .
                'data-target="#confirmModal" data-confirm="' .
                _("Are you sure?") . '"';
        }
        $button_appr_classes .= 'btn btn-success btn-mt';
        
        $withdrawal_data['user_balance_class'] = $user_balance_class;
        $withdrawal_data['button_appr_classes'] = $button_appr_classes;
        $withdrawal_data['button_appr_confirm_text'] = $button_appr_confirm_text;
        
        return $withdrawal_data;
    }
    
    /**
     *
     * @param string $view_template
     * @return int
     */
    public function process_form(string $view_template): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        $withdrawal = Model_Withdrawal_Request::get_data_for_whitelabel_by_token(
            $whitelabel,
            $token
        );
        
        if ($withdrawal === null ||
            (int)$withdrawal['whitelabel_id'] !== (int)$whitelabel['id']
        ) {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        $start_url = '/withdrawals';

        $back_to_list_url = $start_url;
        $back_to_list_url .= Lotto_View::query_vars();

        $withdrawal_urls = [
            'back_to_list' => $back_to_list_url
        ];

        $withdrawal_data = $this->prepare_withdrawal_data_to_show($withdrawal);

        $source = Session::get("source");

        $show_cancel = true;
        
        if ($source != "admin" && Helpers_Whitelabel::is_V1((int)$whitelabel['type'])) {
            $show_cancel = false;
        }
        
        $this->inside = View::forge($view_template);
        $this->inside->set("withdrawal_urls", $withdrawal_urls);
        $this->inside->set("withdrawal_data", $withdrawal_data);
        $this->inside->set("show_cancel", $show_cancel);

        return self::RESULT_OK;
    }
}
