<?php

/**
 *
 */
trait Presenter_Traits_Whitelabel_Reports_Payouts
{
    /**
     * Prepare payouts.
     * @return array prepared payouts.
     */
    private function prepare_payouts(): array
    {
        $prepared_payouts = [];
        
        // go over every row of payouts and prepare them
        foreach ($this->payouts as $key => $payout) {
            $payout['date'] = Lotto_View::format_date(
                $payout['date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE
            );

            $amount_manager_text = Lotto_View::format_currency(
                $payout['amount_manager'],
                $payout['manager_currency_code'],
                true
            );
            $amounts_other = '';
            if ($payout['manager_currency_code'] !== $payout['aff_payout_currency_code']) {
                $amount_text = Lotto_View::format_currency(
                    $payout['amount'],
                    $payout['aff_payout_currency_code'],
                    true
                );
                $amounts_other = _("Affiliate currency") . ": " . $amount_text;
            }
            $payout['amount_manager'] = $amount_manager_text;
            $payout['amounts_other'] = $amounts_other;

            $payout['commissions'] = Lotto_View::format_number($payout['commissions']);
            
            $is_paidout_t = $payout['is_paidout'];
            $payout['is_paidout_class'] = Lotto_View::show_boolean_class($is_paidout_t);

            $payout['is_paidout_span'] = Lotto_View::show_boolean($is_paidout_t);
            
            $dstart = new DateTime($payout['date'], new DateTimeZone("UTC"));

            $month = $dstart->format('n');
            $year = $dstart->format('Y');
            $month--;
            if ($month == 0) {
                $month = 12;
                $year--;
            }

            $dstart->setDate($year, $month, 1);
            $dend = clone $dstart;
            $dend->setTime(23, 59, 59);
            $dend->setDate($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
            
            $report_url = $this->start_url . "/reports/?filter[range_start]=";
            $report_url .= urlencode($dstart->format("m/d/Y"));
            $report_url .= "&amp;filter[range_end]=";
            $report_url .= urlencode($dend->format("m/d/Y"));
            
            $view_url = $this->start_url . '?filter[id]=';
            $token = '';
            if (!empty($payout['token'])) {
                $token = $payout['token'];
            }
            $view_url .= strtoupper($token);
            $payout['view_url'] = $view_url;

            $report_url .= "&amp;filter[aff]=";
            $report_url .= $token;
            $payout['report_url'] = $report_url;

            $full_aff_name_t = '';
            if (!empty($payout['name']) || !empty($payout['surname'])) {
                $full_aff_name_t = $payout['name'];
                $full_aff_name_t .= ' ';
                $full_aff_name_t .= $payout['surname'];
            } else {
                $full_aff_name_t = _("anonymous");
            }
            $full_aff_name_t .= " &bull; ";
            $full_aff_name_t .= $payout['login'];
            $payout['full_aff_name'] = Security::htmlentities($full_aff_name_t);

            $is_confirmed_t = $payout['is_confirmed'];
            $payout['is_confirmed_class'] = Lotto_View::show_boolean_class($is_confirmed_t);

            $payout['is_confirmed_span'] = Lotto_View::show_boolean($is_confirmed_t);

            $email_aff_t = '';
            if (!empty($payout['email'])) {
                $email_aff_t = $payout['email'];
            }
            $payout['email'] = Security::htmlentities($email_aff_t);

            $payment_t = _("Not defined");
            if (!empty($payout['withdrawal_data']) &&
                !empty($this->kwithdrawals[$payout['whitelabel_aff_withdrawal_id']])
            ) {
                $withdrawal_t = $payout['withdrawal_data'];
                $withdrawal = unserialize($withdrawal_t);
                $withdrawal_payment_id = $this->kwithdrawals[$payout['whitelabel_aff_withdrawal_id']]['withdrawal_id'];

                switch ($withdrawal_payment_id) {
                    case Helpers_Withdrawal_Method::WITHDRAWAL_BANK:
                        $payment_t = _("Method") . ': ' . _("Bank Account");
                        $payment_t .= "<br>";
                        $payment_t .= _("Name") . ': ' . Security::htmlentities($withdrawal['name']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Surname") . ': ' . Security::htmlentities($withdrawal['surname']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Account Number") . ': ' . Security::htmlentities($withdrawal['account_no']);
                        $payment_t .= "<br>";
                        $payment_t .= _("SWIFT Code") . ': ' . Security::htmlentities($withdrawal['account_swift']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Bank Name") . ': ' . Security::htmlentities($withdrawal['bank_name']);
                        break;
                    case Helpers_Withdrawal_Method::WITHDRAWAL_SKRILL:
                        $payment_t = _("Method") . ': ' . _("Skrill");
                        $payment_t .= "<br>";
                        $payment_t .= _("Name") . ': ' . Security::htmlentities($withdrawal['name']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Surname") . ': ' . Security::htmlentities($withdrawal['surname']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Skrill e-mail") . ': ' . Security::htmlentities($withdrawal['skrill_email']);
                        break;
                    case Helpers_Withdrawal_Method::WITHDRAWAL_NETELLER:
                        $payment_t = _("Method") . ': ' . _("Neteller");
                        $payment_t .= "<br>";
                        $payment_t .= _("Name") . ': ' . Security::htmlentities($withdrawal['name']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Surname") . ': ' . Security::htmlentities($withdrawal['surname']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Neteller e-mail") . ': ' . Security::htmlentities($withdrawal['neteller_email']);
                        break;
                    case Helpers_Withdrawal_Method::WITHDRAWAL_BTC:
                        $payment_t = _("Method") . ': ' . _("BTC");
                        $payment_t .= "<br>";
                        if (!empty($withdrawal['name'])):
                            $payment_t .= _("Name") . ': ' . Security::htmlentities($withdrawal['name']);
                            $payment_t .= "<br>";
                        endif;

                        if (!empty($withdrawal['surname'])):
                            $payment_t .= _("Surname") . ': ' . Security::htmlentities($withdrawal['surname']);
                            $payment_t .= "<br>";
                        endif;
                        $payment_t .= _("Bitcoin Address") . ': ' . Security::htmlentities($withdrawal['bitcoin']);
                        break;
                    case Helpers_Withdrawal_Method::WITHDRAWAL_PAYPAL:
                        $payment_t = _("Method") . ': ' . _("PayPal");
                        $payment_t .= "<br>";
                        $payment_t .= _("Name") . ': ' . Security::htmlentities($withdrawal['name']);
                        $payment_t .= "<br>";
                        $payment_t .= _("Surname") . ': ' . Security::htmlentities($withdrawal['surname']);
                        $payment_t .= "<br>";
                        $payment_t .= _("PayPal e-mail") . ': ' . Security::htmlentities($withdrawal['paypal_email']);
                        break;
                }
            }
            $payout['payment'] = $payment_t;

            $payout['is_paidout'] = intval($is_paidout_t);

            $accept_url = $this->start_url . "/payouts/accept/";
            $accept_url .= $key . Lotto_View::query_vars();
            $payout['accept_url'] = $accept_url;
                
            $prepared_payouts[] = $payout;
        }

        // return prepared values
        return $prepared_payouts;
    }
}
