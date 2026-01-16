<?php

/**
 *
 */
trait Traits_Prepare_Withdrawals
{
    private function prepare_whitelabel_withdrawal_data(array &$withdrawal, ?int $whitelabel = null, string $lang_code)
    {
        $withdrawal['full_token'] = $withdrawal['whitelabel_prefix'] . "W" . $withdrawal['token'];
        $user_token = $withdrawal['whitelabel_prefix'] . "U" . $withdrawal['user_token'];
        $withdrawal['user_token_full'] = $user_token;
        $user_fullname = _("Anonymous");
        if (!empty($withdrawal['name']) || !empty($withdrawal['surname'])) {
            $user_fullname = $withdrawal['name'] . ' ' . $withdrawal['surname'];
        }
        $withdrawal['user_fullname'] = $user_fullname;

        $user_login = "-";
        if (isset($withdrawal['user_login'])) {
            $user_login = $withdrawal['user_login'];
        }
        $withdrawal['user_login'] = $user_login;

        switch ($withdrawal['status']) {
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING:
                $withdrawal['status_display'] = _("Pending");
            break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED:
                $withdrawal['status_display'] = _("Approved");
            break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED:
                $withdrawal['status_display'] = _("Declined");
            break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED:
                $withdrawal['status_display'] = _("Canceled");
        }

        $data = unserialize($withdrawal['data']);
        $withdrawal['data'] = [];
        $withdrawal['request_details'] = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

        $names_temp = [
            'neteller_email' => _("Neteller e-mail"),
            'skrill_email' => _("Skrill e-mail"),
            'bitcoin' => _("Bitcoin wallet"),
            'account_no' => _("Account IBAN"),
            'account_swift' => _("SWIFT number"),
            'bank_name' => _("Bank name"),
            'paypal_email' => _("PayPal e-mail"),
            'fairox_account_id' => _("Fairox Account Id"),
            'usdt_wallet_type' => _("USDT Wallet Type"),
            'usdt_wallet_address' => _("USDT Wallet Address"),
            'email' => _("Email"),
            'bank_address' => _("Bank address"),
            'name' => _("Name"),
            'surname' => _("Surname"),
            'address' => _("Address"),
            'exchange' => _("Exchange"),
        ];

        foreach ($data as $key => $value) {
            $label = Security::htmlentities($key);

            if (isset($names_temp[$key])) {
                $label = Security::htmlentities($names_temp[$key]);
            }

            $withdrawal['data'][] = [
                'label' => $label,
                'value' => htmlspecialchars($value),
            ];

        }

        if ((float)$withdrawal['amount'] > (float)$withdrawal['user_balance'] &&
            (int)$withdrawal['status'] !== Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED) {
            $withdrawal['user_balance_class_danger'] = true;
        }

        $withdrawal['method_name'] = _($withdrawal['method']);

        $fmt = new NumberFormatter($lang_code, NumberFormatter::CURRENCY);
        $currency = "USD";
        $amount = $withdrawal['amount_usd'];
        $balance = $withdrawal['user_balance'];
        $balance_currency_tab = [
            'id' => $withdrawal['user_currency_id'],
            'code' => $withdrawal['user_currency_code'],
            'rate' => $withdrawal['user_currency_rate'],
        ];

        if ($whitelabel) {
            $currency = $withdrawal['whitelabel_currency_code'];
            $amount = $withdrawal['amount_manager'];
            $balance = Helpers_Currency::get_recalculated_to_given_currency(
                $withdrawal['user_balance'],
                $balance_currency_tab,
                $withdrawal['whitelabel_currency_code']
            );
        } else {
            $balance = Helpers_Currency::get_recalculated_to_given_currency(
                $withdrawal['user_balance'],
                $balance_currency_tab,
                $currency
            );
        }

        $withdrawal['amount_display'] = Lotto_View::format_currency(
            $amount,
            $currency,
            true
        );

        $withdrawal['user_balance_display'] = Lotto_View::format_currency(
            $balance,
            $currency,
            true
        );

        if ($withdrawal['user_currency_code'] !== $currency) {
            $amount_text = Lotto_View::format_currency(
                $withdrawal['amount'],
                $withdrawal['user_currency_code'],
                true
            );
            $withdrawal['amount_additional_text'] = _("User currency") .
            ": " . $amount_text;

            $user_balance_text = Lotto_View::format_currency(
                $withdrawal['user_balance'],
                $withdrawal['user_currency_code'],
                true
            );
            $withdrawal['balance_additional_text'] = _("User currency") .
            ": " . $user_balance_text;
        }
    }
}
