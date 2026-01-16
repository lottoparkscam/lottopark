<?php

use Carbon\Carbon;
use Fuel\Core\Security;

trait Presenter_Traits_Whitelabel_Reports_Commissions
{
    /**
     * Prepare commissions.
     * @return array prepared commissions.
     */
    private function prepare_commissions(): array
    {
        $prepared_commissions = [];
        
        // go over every row of commissions and prepare them
        foreach ($this->commissions as $commission) {
            $aff_full_name = '';
            if (!empty($commission['aff_name']) || !empty($commission['aff_surname'])) {
                $aff_full_name .= $commission['aff_name'] . ' ' . $commission['aff_surname'];
            } else {
                $aff_full_name .= _("anonymous");
            }
            $aff_full_name .= ' &bull; ';
            $aff_full_name .= $commission['aff_login'];
            $commission['aff_full_name'] = Security::htmlentities($aff_full_name);

            $commission['aff_is_confirmed_span'] = $this->prepare_bool($commission['aff_is_confirmed']);

            $user_full_name = '';
            if (!empty($commission['name']) || !empty($commission['surname'])) {
                $user_full_name .= $commission['name'];
                $user_full_name .= ' ';
                $user_full_name .= $commission['surname'];
            } else {
                $user_full_name .= _("anonymous");
            }
            $user_full_name .= ' &bull; ';
            $user_full_name .= $this->whitelabel['prefix'] . 'U' . $commission['token'];
            $commission['user_full_name'] = Security::htmlentities($user_full_name);
            
            $user_is_confirmed_t = $commission['is_confirmed'];
            $commission['user_is_confirmed_span'] = $this->prepare_bool($user_is_confirmed_t);

            $user_email_t = '';
            if (!empty($commission['email'])) {
                $user_email_t = $commission['email'];
            }
            $commission['user_email'] = Security::htmlentities($user_email_t);
            
            $transaction_t = $this->whitelabel['prefix'];
            $add_trans_text = 'D';
            if ($commission['ttype'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $add_trans_text = 'P';
            }
            $transaction_t .= $add_trans_text;
            $transaction_t .= $commission['ttoken'];
            $commission['transaction_id'] = $transaction_t;

            $aff_type_t = _("FTP");
            if ($commission['type'] == Helpers_General::TYPE_AFF_SALE) {
                $aff_type_t = _("Sale");
            }
            $commission['aff_type'] = $aff_type_t;

            $tier_t = _("Tier");
            $tier_t .= ": ";
            $tier_t .= $commission['tier'];
            $commission['tier'] = $tier_t;

            $commission['date_confirmed'] = Lotto_View::format_date(
                $commission['date_confirmed'],
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT
            );
            
            $amount_manager_text = Lotto_View::format_currency(
                $commission['amount_manager'],
                $commission['manager_currency_code'],
                true
            );
            $cost_manager_text = Lotto_View::format_currency(
                $commission['cost_manager'],
                $commission['manager_currency_code'],
                true
            );
            $payment_cost_manager_text = Lotto_View::format_currency(
                $commission['payment_cost_manager'],
                $commission['manager_currency_code'],
                true
            );
            $real_income_manager = round(
                $commission['income_manager'] - $commission['payment_cost_manager'],
                2
            );
            $real_income_manager_text = Lotto_View::format_currency(
                $real_income_manager,
                $commission['manager_currency_code'],
                true
            );
            $commission_manager_text = Lotto_View::format_currency(
                $commission['commission_manager'],
                $commission['manager_currency_code'],
                true
            );

            $amounts_other = '';
            $costs_other = '';
            $payment_costs_other = '';
            $real_incomes_other = '';
            $commissions_other = '';
            if ($commission['manager_currency_code'] !== $commission['user_currency_code']) {
                $amount_text = Lotto_View::format_currency(
                    $commission['amount'],
                    $commission['user_currency_code'],
                    true
                );
                $amounts_other = _("User currency") . ": " . $amount_text;
                
                $cost_text = Lotto_View::format_currency(
                    $commission['cost'],
                    $commission['user_currency_code'],
                    true
                );
                $costs_other = _("User currency") . ": " . $cost_text;
                
                $payment_cost_text = Lotto_View::format_currency(
                    $commission['payment_cost'],
                    $commission['user_currency_code'],
                    true
                );
                $payment_costs_other = _("User currency") . ": " . $payment_cost_text;
                
                $real_income = round(
                    $commission['income'] - $commission['payment_cost'],
                    2
                );
                $real_income_text = Lotto_View::format_currency(
                    $real_income,
                    $commission['user_currency_code'],
                    true
                );
                $real_incomes_other = _("User currency") . ": " . $real_income_text;
                
                $commission_text = Lotto_View::format_currency(
                    $commission['commission'],
                    $commission['user_currency_code'],
                    true
                );
                $commissions_other = _("User currency") . ": " . $commission_text;
            }
            
            $commission['amount_manager'] = $amount_manager_text;
            $commission['amounts_other'] = $amounts_other;
            
            $commission['cost_manager'] = $cost_manager_text;
            $commission['costs_other'] = $costs_other;
            
            $commission['payment_cost_manager'] = $payment_cost_manager_text;
            $commission['payment_costs_other'] = $payment_costs_other;

            $commission['real_income_manager'] = $real_income_manager_text;
            $commission['real_incomes_other'] = $real_incomes_other;

            $commission['commission_manager'] = $commission_manager_text;
            $commission['commissions_other'] = $commissions_other;
            
            $commission['user_is_accepted'] = $commission['is_accepted'];

            $aff_token = $commission['aff_token'];
            $view_aff_url = "/affs?filter[id]=";
            $view_aff_url .= strtoupper($aff_token);
            $commission['view_aff_url'] = $view_aff_url;

            $view_user_url = "/users?filter[id]=";
            $view_user_url .= $commission['token'];
            $commission['view_user_url'] = $view_user_url;

            $view_tickets_url = "/tickets?filter[userid]=";
            $view_tickets_url .= $commission['token'];
            $commission['view_tickets_url'] = $view_tickets_url;

            $view_transaction_url = "/";
            $additional_t = "deposits";
            if ($commission['ttype'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $additional_t = 'transactions';
            }
            $view_transaction_url .= $additional_t;
            $view_transaction_url .= "?filter[id]=";
            $view_transaction_url .= $commission['ttoken'];
            $commission['view_transaction_url'] = $view_transaction_url;

            $dt_now = new DateTime("now", new DateTimeZone("UTC"));
            $dt_now->setDate($dt_now->format('Y'), $dt_now->format('m'), 1);
            $dt_now->setTime(0, 0, 0);
            $dt_item = new DateTime($commission['date_confirmed'], new DateTimeZone("UTC"));
            $dt_item->setDate($dt_item->format('Y'), $dt_item->format('m'), 1);
            $dt_item->setTime(0, 0, 0);

            $accept_disabled = '';
            $delete_disabled = '';
            if ($dt_item < $dt_now) {
                $accept_disabled = ' disabled="disabled"';
                $delete_disabled = ' disabled="disabled"';
            }
            $commission['accept_disabled'] = $accept_disabled;
            $commission['delete_disabled'] = $delete_disabled;

            $accept_url = "/affs/commissions/accept/";
            $accept_url .= $commission['id'];
            $accept_url .= Lotto_View::query_vars();
            $commission['accept_url'] = $accept_url;

            $delete_url = "/affs/commissions/delete/";
            $delete_url .= $commission['id'];
            $delete_url .= Lotto_View::query_vars();
            $commission['delete_url'] = $delete_url;

            $prepared_commissions[] = $commission;
        }

        // return prepared values
        return $prepared_commissions;
    }

    private function prepareCasinoCommissions(): array
    {
        $prepared_commissions = [];
        foreach ($this->casinoCommissions as $commission) {
            $affUrl = "/affs?filter[id]=";
            $affUrl .= strtoupper($commission['token']);
            $commission['aff_url'] = $affUrl;
            $commission['aff_is_confirmed'] = $this->prepare_bool($commission['aff_is_confirmed']);

            $affFullName = '';
            if (!empty($commission['name']) || !empty($commission['surname'])) {
                $affFullName .= $commission['name'] . ' ' . $commission['surname'];
            } else {
                $affFullName .= _('Anonymous');
            }
            $affFullName .= ' &bull; ' . $commission['login'];
            $commission['aff_full_name'] = $affFullName;

            $commission['lead_email'] = $commission['lead_email'] ?? '';
            $commission['lead_name'] = $commission['lead_name'] ?? '';
            $commission['lead_email'] = Security::htmlentities($commission['lead_email']);
            $commission['lead_is_confirmed'] = $this->prepare_bool($commission['is_confirmed']);

            $userUrl = "/users?filter[id]=";
            $userUrl .= $commission['lead_token'];
            $commission['user_url'] = $userUrl;

            $ticketsUrl = "/tickets?filter[userid]=";
            $ticketsUrl .= $commission['lead_token'];
            $commission['tickets_url'] = $ticketsUrl;
            $commission['ggr'] = $commission['ggr_usd'];
            $commission['ggr_amount'] = $commission['ggr'];
            $commission['commission'] = $commission['daily_commission_usd'];
            $commission['commission_amount'] = $commission['commission'];
            $commission['show_ggr'] = $commission['ggr'] > 0;

            if ($commission['show_ggr']) {
                $commission['ggr'] = Helpers_Currency::convert_to_any(
                    $commission['ggr'],
                    $commission['user_currency_code'],
                    $commission['manager_currency_code']
                );

                $commission['ggr'] = Lotto_View::format_currency(
                    $commission['ggr'],
                    $commission['manager_currency_code'],
                    true
                );
            }

            $commission['show_commission'] = $commission['commission'] > 0;
            if ($commission['show_commission']) {
                $commission['commission'] = Helpers_Currency::convert_to_any(
                    $commission['commission'],
                    $commission['user_currency_code'],
                    $commission['manager_currency_code']
                );

                $commission['commission'] = Lotto_View::format_currency(
                    $commission['commission'],
                    $commission['manager_currency_code'],
                    true
                );
            }

            $shouldAddUserCurrencyAmountInfo = $commission['manager_currency_code'] !== $commission['user_currency_code'];
            $commissions_other = '';
            $ngrInUserCurrency = '';

            if ($shouldAddUserCurrencyAmountInfo) {
                $commissionAmount = Helpers_Currency::convert_to_any(
                    $commission['commission_amount'],
                    $commission['manager_currency_code'],
                    $commission['user_currency_code'],
                    false,
                    10
                );

                $commission_text = Lotto_View::format_currency(
                    $commissionAmount,
                    $commission['user_currency_code'],
                    true
                );
                $commissions_other = _("User currency") . ": " . $commission_text;

                $ngrInUserCurrency = Helpers_Currency::convert_to_any(
                    $commission['ggr_amount'],
                    $commission['manager_currency_code'],
                    $commission['user_currency_code'],
                    false,
                    10
                );

                $ngrInUserCurrency = Lotto_View::format_currency(
                    $ngrInUserCurrency,
                    $commission['user_currency_code'],
                    true
                );
                
                $ngrInUserCurrency = _("User currency") . ": " . $ngrInUserCurrency;
            }

            $commission['commissions_other'] = $commissions_other;
            $commission['ggrInUserCurrency'] = $ngrInUserCurrency;

            $prepared_commissions[] = $commission;
        }

        return $prepared_commissions;
    }
}
