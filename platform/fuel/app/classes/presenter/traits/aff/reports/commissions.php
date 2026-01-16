<?php

use Carbon\Carbon;
use Fuel\Core\Security;

/** Trait for preparation of commissions in presenter. */
trait Presenter_Traits_Aff_Reports_Commissions
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
            // todo: prepare rest of data here, instead of view
            $commission['lead_name'] = $this->prepare_lead_name($commission);
            $commission['lead_email'] = Security::htmlentities($commission['lead_email']);
            $commission['whitelabel_id'] = $this->whitelabel['id'];
            $user_full_name = $this->whitelabel['prefix'] . 'U' . $commission['token'];
            $commission['user_full_name'] = Security::htmlentities($user_full_name);

            $transaction_t = $this->whitelabel['prefix'];
            $add_trans_text = 'D';
            if ($commission['ttype'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $add_trans_text = 'P';
            }
            $transaction_t .= $add_trans_text;
            $transaction_t .= $commission['ttoken'];
            $commission['transaction_id'] = $transaction_t;

            $medium_t = '';
            if (!empty($commission['medium'])) {
                $medium_t = $commission['medium'];
            }
            $commission['medium'] = Security::htmlentities($medium_t);

            $campaign_t = '';
            if (!empty($commission['campaign'])) {
                $campaign_t = $commission['campaign'];
            }
            $commission['campaign'] = Security::htmlentities($campaign_t);

            $content_t = '';
            if (!empty($commission['content'])) {
                $content_t = $commission['content'];
            }
            $commission['content'] = Security::htmlentities($content_t);

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
                IntlDateFormatter::MEDIUM,
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

            $prepared_commissions[] = $commission; // add new entry to prepared commissions
        }

        // return prepared values
        return $prepared_commissions;
    }

    private function prepareCasinoCommissions(): array
    {
        $prepared_commissions = [];
        // go over every row of commissions and prepare them
        foreach ($this->casinoCommissions as $commission) {
            $affFullName = '';
            if (!empty($commission['aff_name']) || !empty($commission['aff_surname'])) {
                $affFullName .= $commission['aff_name'] . ' ' . $commission['aff_surname '];
            } else {
                $affFullName .= _('Anonymous');
            }
            $affFullName .= ' &bull; ' . $commission['login'];
            $commission['aff_full_name'] = $affFullName;

            $commission['lead_name'] = $this->prepare_lead_name($commission);
            $commission['lead_email'] = Security::htmlentities($commission['lead_email']);

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

            $medium_t = '';
            if (!empty($commission['medium'])) {
                $medium_t = $commission['medium'];
            }
            $commission['medium'] = Security::htmlentities($medium_t);

            $campaign_t = '';
            if (!empty($commission['campaign'])) {
                $campaign_t = $commission['campaign'];
            }
            $commission['campaign'] = Security::htmlentities($campaign_t);

            $content_t = '';
            if (!empty($commission['content'])) {
                $content_t = $commission['content'];
            }
            $commission['content'] = Security::htmlentities($content_t);

            $shouldAddUserCurrencyAmountInfo = $commission['manager_currency_code'] !== $commission['user_currency_code'];
            $commissions_other = '';
            $ggrInUserCurrency = '';
            if ($shouldAddUserCurrencyAmountInfo) {
                $commission_text = Lotto_View::format_currency(
                    $commission['commission_amount'],
                    $commission['user_currency_code'],
                    true
                );
                $commissions_other = _("User currency") . ": " . $commission_text;

                $ggrInUserCurrency = Lotto_View::format_currency(
                    $commission['ggr_amount'],
                    $commission['user_currency_code'],
                    true
                );
                $ggrInUserCurrency = _("User currency") . ": " . $ggrInUserCurrency;
            }

            $commission['commissions_other'] = $commissions_other;
            $commission['ggrInUserCurrency'] = $ggrInUserCurrency;

            $prepared_commissions[] = $commission; // add new entry to prepared commissions
        }

        // return prepared values
        return $prepared_commissions;
    }
}
