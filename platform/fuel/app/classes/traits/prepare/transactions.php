<?php

/**
 *
 */
trait Traits_Prepare_Transactions
{
    private function prepare_whitelabel_transaction_data(array &$transaction, ?int $whitelabel = null, string $lang_code)
    {
        $fullToken = $transaction['token'];
        if ($transaction['type'] === "0") {
            $fullToken = $transaction['whitelabel_prefix'] . "P" . $transaction['token'];
        } elseif ($transaction['type'] === "1") {
            $fullToken = $transaction['whitelabel_prefix'] . "D" . $transaction['token'];
        }
        $transaction['full_token'] = $fullToken;
        $user_token = $transaction['whitelabel_prefix'] . "U" . $transaction['user_token'];
        $transaction['user_token_full'] = $user_token;
        if ($transaction['user_name'] === "" && $transaction['user_surname'] === "") {
            $transaction['user_name'] = _('anonymous');
        }
        $user_login = "-";
        if (isset($transaction['user_login'])) {
            $user_login = $transaction['user_login'];
        }
        $transaction['user_login'] = $user_login;
        if (!isset($transaction['tickets_count'])) {
            $transaction['tickets_count'] = '-';
            $transaction['tickets_processed_count'] = '-';
        }
        if (!isset($transaction['tickets_processed_count'])) {
            $transaction['tickets_processed_count'] = 0;
        }
        switch ($transaction['status']) {
            case Helpers_General::STATUS_TRANSACTION_PENDING:
                $transaction['status_display'] = _("Pending");
            break;
            case Helpers_General::STATUS_TRANSACTION_APPROVED:
                $transaction['status_display'] = _("Approved");
            break;
            case Helpers_General::STATUS_TRANSACTION_ERROR:
                $transaction['status_display'] = _("Error");
        }

        $currency = 'USD';
        $amount = $transaction['amount_usd'];
        $bonus_amount = $transaction['bonus_amount_usd'];
        $income = $transaction['income_usd'];
        $cost = $transaction['cost_usd'];
        $payment_cost = $transaction['payment_cost_usd'];
        $margin = $transaction['margin_usd'];
        $fmt = new NumberFormatter($lang_code, NumberFormatter::CURRENCY);
        if ($whitelabel) {
            $currency = $transaction['whitelabel_currency_code'];
            $amount = $transaction['amount_manager'];
            $bonus_amount = $transaction['bonus_amount_manager'];
            $income = $transaction['income_manager'];
            $cost = $transaction['cost_manager'];
            $payment_cost = $transaction['payment_cost_manager'];
            $margin = $transaction['margin_manager'];
        }
        $transaction['amount_display'] = $fmt->formatCurrency($amount, $currency);
        $transaction['bonus_amount_display'] = $fmt->formatCurrency($bonus_amount, $currency);
        $transaction['income_display'] = $fmt->formatCurrency($income, $currency);
        $transaction['cost_display'] = $fmt->formatCurrency($cost, $currency);
        $transaction['payment_cost_display'] = $fmt->formatCurrency($payment_cost, $currency);
        $transaction['margin_display'] = $fmt->formatCurrency($margin, $currency);

        if (empty($transaction['method'])) {
            switch ($transaction['payment_method_type']) {
                case Helpers_General::PAYMENT_TYPE_BALANCE:
                    $transaction['method'] = _("Balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                    $transaction['method'] = _("Bonus balance");
                    break;
            }
        }
    }
}
