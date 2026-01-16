<?php

use Helpers\CurrencyHelper;

/**
 * Description of Forms_Whitelabel_Transaction_View
 */
class Forms_Whitelabel_Transactions_View extends Forms_Main
{
    /**
     * This is in fact token
     *
     * @var int
     */
    private $param_id;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @var string
     */
    private $rparam = "";
    
    /**
     *
     * @param int $param_id
     * @param array $whitelabel
     * @param string $rparam
     */
    public function __construct(
        int $param_id = null,
        array $whitelabel = [],
        string $rparam = "transactions"
    ) {
        $this->param_id = $param_id;
        $this->whitelabel = $whitelabel;
        $this->rparam = $rparam;
    }
    
    /**
     *
     * @return null|array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @param array $whitelabel
     */
    public function set_whitelabel(array $whitelabel): Forms_Whitelabel_Transactions_View
    {
        $this->whitelabel = $whitelabel;
        
        return $this;
    }
    
    /**
     *
     * @return int
     */
    public function get_param_id(): int
    {
        return $this->param_id;
    }

    /**
     *
     * @param int $param_id
     */
    public function set_param_id(int $param_id): Forms_Whitelabel_Transactions_View
    {
        $this->param_id = $param_id;
        
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function get_rparam(): string
    {
        return $this->rparam;
    }

    /**
     *
     * @param array $transaction
     * @return array
     */
    private function prepare_transaction_view_data(array $transaction): array
    {
        $transaction_data = [];
        $whitelabel = $this->get_whitelabel();
        
        $user = Model_Whitelabel_User::find_by_pk($transaction['whitelabel_user_id']);
        
        $user_currency = CurrencyHelper::getCurrentCurrency()->to_array();
        
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $user_currency
        );
        
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        $whitelabel_languages_indexed_by_id = Lotto_Helper::prepare_languages($whitelabel_languages);
        $ccmethod_ids = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
        $cc_gateways = Lotto_Helper::get_cc_gateways();
        
        $ccmethods_merchant = [];
        foreach ($ccmethod_ids as $ccmethod) {
            $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
        }
        
        $full_token = $whitelabel['prefix'];
        if ($transaction['type'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $full_token .= 'P';
        } else {
            $full_token .= 'D';
        }
        $full_token .= $transaction["token"];
        $transaction_data['full_token'] = Security::htmlentities($full_token);

        $user_full_token = '-';
        if (!empty($user['token'])) {
            $user_full_token = $whitelabel['prefix'] . 'U' . $user['token'];
        }
        $transaction_data['user_full_token'] = Security::htmlentities($user_full_token);

        $first_name = _("Anonymous");
        if (!empty($user['name'])) {
            $first_name = $user['name'];
        }
        $transaction_data['first_name'] = Security::htmlentities($first_name);

        $surname = _("Anonymous");
        if (!empty($user['surname'])) {
            $surname = $user['surname'];
        }
        $transaction_data['surname'] = Security::htmlentities($surname);

        $transaction_data['email'] = Security::htmlentities($user['email']);
        
        $login = "-";
        if (!empty($user['login'])) {
            $login = $user['login'];
        }
        $transaction_data['user_login'] = Security::htmlentities($login);

        $transaction_data['date'] = Lotto_View::format_date(
            $transaction['date'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        if (!empty($transaction['date_confirmed'])) {
            $transaction_data['date_confirmed'] = Lotto_View::format_date(
                $transaction['date_confirmed'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }

        if (!empty($transaction['payment_method_type'])) {
            switch ($transaction['payment_method_type']) {
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                    $transaction_data['payment_method_type'] = _("Bonus balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_BALANCE:
                    $transaction_data['payment_method_type'] = _("Balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_CC:
                    $transaction_data['payment_method_type'] = _("Credit Card");
                    break;
                case Helpers_General::PAYMENT_TYPE_OTHER:
                    $transaction_payment_id = $transaction['whitelabel_payment_method_id'];
                    if (!isset($whitelabel_payment_methods_with_currency[$transaction_payment_id])) {
                        exit("There is a problem on server");
                    }
                    $method_payment_tab = $whitelabel_payment_methods_with_currency[$transaction_payment_id];
                    $lang_tab = $whitelabel_languages_indexed_by_id[$method_payment_tab['language_id']];

                    $text_to_show_t = '';
                    if (!empty($method_payment_tab['pname'])) {
                        $text_to_show_t = $method_payment_tab['name'];
                        $text_to_show_t .= " (" . $method_payment_tab['pname'] . ")";
                    } else {
                        $text_to_show_t = $method_payment_tab['name'];
                    }
                    
                    $text_to_show = Security::htmlentities($text_to_show_t);
                    $text_to_show .= " - ";
                    $text_to_show .= Lotto_View::format_language($lang_tab['code']);

                    $transaction_data['payment_method_type'] = $text_to_show;
                    break;
            }
        }

        if ($transaction['payment_method_type'] == Helpers_General::PAYMENT_TYPE_CC &&
            !empty($transaction['whitelabel_cc_method_id']) &&
            !empty($ccmethod_ids[$transaction['whitelabel_cc_method_id']]) &&
            !empty($cc_gateways[$ccmethod_ids[$transaction['whitelabel_cc_method_id']]['method']])
        ) {
            $trans_cc_method_id = $transaction['whitelabel_cc_method_id'];
            $cc_method_name = $ccmethod_ids[$trans_cc_method_id]['method'];
            $gateway_method = $cc_gateways[$cc_method_name];
            $transaction_data['gateway_payment_method'] = $gateway_method;
        }

        $amount_text = Lotto_View::format_currency(
            $transaction['amount'],
            $transaction['transaction_currency_code'],
            true
        );
        $amount_manager = Lotto_View::format_currency(
            $transaction['amount_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['amount_manager'] = Security::htmlentities($amount_manager);
        
        $other_amounts = [];

        $bonus_amount_text = Lotto_View::format_currency(
            $transaction['bonus_amount'],
            $transaction['transaction_currency_code'],
            true
        );
        $bonus_amount_manager = Lotto_View::format_currency(
            $transaction['bonus_amount_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['bonus_amount_manager'] = Security::htmlentities($bonus_amount_manager);
        
        $other_bonus_amounts = [];
        
        if ($transaction['transaction_currency_code'] !== $transaction['manager_currency_code']) {
            $other_amounts[] = _("User currency") .
                    ": " . $amount_text;
            $other_bonus_amounts[] = _("User currency") .
                    ": " . $bonus_amount_text;
        }
        
        $amount_payment_text = "";
        $bonus_amount_payment_text = "";
        // If transaction currency is different than payment currency
        if ($transaction['payment_currency_code'] !== $transaction['manager_currency_code'] &&
            $transaction['payment_currency_code'] !== $transaction['transaction_currency_code']
        ) {
            $amount_payment_text = Lotto_View::format_currency(
                $transaction['amount_payment'],
                $transaction['payment_currency_code'],
                true
            );
            $bonus_amount_payment_text = Lotto_View::format_currency(
                $transaction['bonus_amount_payment'],
                $transaction['payment_currency_code'],
                true
            );
            $other_amounts[] = _("Payment currency") .
                ": " . $amount_payment_text;
            
            $other_bonus_amounts[] = _("Payment currency") .
                ": " . $bonus_amount_payment_text;
        }
        
        $user_amounts = implode("<br>", $other_amounts);
        $bonus_amounts = implode("<br>", $other_bonus_amounts);
        
        $transaction_data['user_amounts'] = Security::htmlentities($user_amounts);
        $transaction_data['bonus_amounts'] = Security::htmlentities($bonus_amounts);
        
        $income_manager = Lotto_View::format_currency(
            $transaction['income_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['income_manager'] = Security::htmlentities($income_manager);
        
        $cost_manager = Lotto_View::format_currency(
            $transaction['cost_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['cost_manager'] = Security::htmlentities($cost_manager);
        
        $payment_cost_manager = Lotto_View::format_currency(
            $transaction['payment_cost_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['payment_cost_manager'] = Security::htmlentities($payment_cost_manager);
        
        $margin_manager = Lotto_View::format_currency(
            $transaction['margin_manager'],
            $transaction['manager_currency_code'],
            true
        );
        $transaction_data['margin_manager'] = Security::htmlentities($margin_manager);
        
        if ($transaction['transaction_currency_code'] !== $transaction['manager_currency_code']) {
            $income_text = Lotto_View::format_currency(
                $transaction['income'],
                $transaction['transaction_currency_code'],
                true
            );
            $income = _("User currency") . ": " . $income_text;
            $transaction_data['income'] = Security::htmlentities($income);
            
            $cost_text = Lotto_View::format_currency(
                $transaction['cost'],
                $transaction['transaction_currency_code'],
                true
            );
            $cost = _("User currency") . ": " . $cost_text;
            $transaction_data['cost'] = Security::htmlentities($cost);
            
            $payment_cost_text = Lotto_View::format_currency(
                $transaction['payment_cost'],
                $transaction['transaction_currency_code'],
                true
            );
            $payment_cost = _("User currency") . ": " . $payment_cost_text;
            $transaction_data['payment_cost'] = Security::htmlentities($payment_cost);
            
            $margin_text = Lotto_View::format_currency(
                $transaction['margin'],
                $transaction['transaction_currency_code'],
                true
            );
            $margin = _("User currency") . ": " . $margin_text;
            $transaction_data['margin'] = Security::htmlentities($margin);
        }
        
        switch ($transaction['status']) {
            case Helpers_General::STATUS_TRANSACTION_PENDING:
                $transaction_data['status_show_class'] = "";
                $transaction_data['status'] = _("Pending");
                break;
            case Helpers_General::STATUS_TRANSACTION_APPROVED:
                $transaction_data['status_show_class'] = "text-success";
                $transaction_data['status'] = _("Approved");
                break;
            case Helpers_General::STATUS_TRANSACTION_ERROR:
                $transaction_data['status_show_class'] = "text-danger";
                $transaction_data['status'] = _("Error");
                break;
        }

        if (!empty($transaction['transaction_out_id'])) {
            $transaction_data['transaction_out_id'] = Security::htmlentities(
                $transaction['transaction_out_id']
            );
        }
        
        $payments_methods_strings = Helpers_Payment_Method::get_all_methods_with_URI();
        
        $transaction_data['payment_method_details_string'] = "";
        if (isset($transaction) && isset($transaction['payment_method_type'])) {
            $payment_type = $transaction['payment_method_type'];
            $payment_method_id = 0;
            
            switch ($payment_type) {
                case Helpers_General::PAYMENT_TYPE_CC:
                    if (isset($ccmethod_ids) &&
                        !empty($transaction['whitelabel_cc_method_id']) &&
                        !empty($ccmethod_ids[$transaction['whitelabel_cc_method_id']]['method']) &&
                        $ccmethod_ids[$transaction['whitelabel_cc_method_id']]['method'] == Helpers_Payment_Method::CC_EMERCHANT
                    ) {
                        $payment_method_id = Helpers_Payment_Method::CC_EMERCHANT;
                    }
                    break;
                case Helpers_General::PAYMENT_TYPE_OTHER:
                    if (isset($whitelabel_payment_methods_with_currency) &&
                        !empty($transaction['whitelabel_payment_method_id']) &&
                        !empty($whitelabel_payment_methods_with_currency[$transaction['whitelabel_payment_method_id']]['payment_method_id']) &&
                        isset($transaction['additional_data'])
                    ) {
                        $whitelabel_payment_method_id = $transaction['whitelabel_payment_method_id'];
                        $payment_method_id = $whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]['payment_method_id'];
                    }
                    break;
            }

            if (isset($payments_methods_strings[$payment_type][$payment_method_id])) {
                $transaction_data['payment_method_details_string'] = $payments_methods_strings[$payment_type][$payment_method_id];
            }
        }
        
        return $transaction_data;
    }

    public function process_form(string $viewTemplate): int
    {
        $whitelabel = $this->get_whitelabel();
        $rparam = $this->get_rparam();
        
        $transaction = Model_Whitelabel_Transaction::get_single_for_whitelabel_by_token(
            $whitelabel,
            $this->get_param_id()
        );

        if ($transaction === null ||
                count($transaction) === 0 ||
                (int)$transaction['whitelabel_id'] !== (int)$whitelabel['id']
        ) {
            return self::RESULT_INCORRECT_TRANSACTION;
        }
        
        $transactionUrls = [];
        $backUrl = '/';
        if ($rparam == "transactions") {
            $backUrl .= 'transactions';
        } else {
            $backUrl .= 'deposits';
        }
        $backUrl .= Lotto_View::query_vars();
        $transactionUrls['back'] = $backUrl;

        if ((int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE &&
            (int)$transaction['status'] === Helpers_General::STATUS_TRANSACTION_APPROVED
        ) {
            $ticketsUrl = "/tickets?filter[transactionid]=" . $transaction['token'];
            $transactionUrls['tickets_view'] = $ticketsUrl;
        }

        $transactionData = $this->prepare_transaction_view_data($transaction);

        $additionalData = unserialize($transaction['additional_data']);
        $additionalDataJson = json_decode($transaction['additional_data_json'], true);

        $this->inside = View::forge($viewTemplate);
        $this->inside->set_global("type", $rparam);
        $this->inside->set("transaction_data", $transactionData);
        $this->inside->set("adata", $additionalData);
        $this->inside->set("additionalDataJson", $additionalDataJson);
        $this->inside->set("transaction_urls", $transactionUrls);
        
        return self::RESULT_OK;
    }
}
