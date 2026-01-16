<?php

/**
 * Trait for preparation of payouts in presenter.
 */
trait Presenter_Traits_Aff_Reports_Payouts
{

    /**
     * Prepare payouts.
     * @return array prepared payouts.
     */
    private function prepare_payouts(): array
    {
        $prepared_payouts = [];
        
        // go over every row of ftps and prepare them
        foreach ($this->payouts as $payout) {
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
                $amounts_other = _("Affiliate user currency") . ": " . $amount_text;
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
            
            $report_url = "/reports/?filter[range_start]=";
            $report_url .= urlencode($dstart->format("m/d/Y"));
            $report_url .= "&amp;filter[range_end]=";
            $report_url .= urlencode($dend->format("m/d/Y"));
            $payout['report_url'] = $report_url;
            
            $prepared_payouts[] = $payout; // add new entry to prepared payouts
        }
        
        // return prepared values
        return $prepared_payouts;
    }
}
