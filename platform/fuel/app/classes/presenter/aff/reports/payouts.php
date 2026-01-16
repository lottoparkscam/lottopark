<?php

/**
 * Prepare data for views/aff/reports/payouts.
 *
 * @author Marcin
 */
class Presenter_Aff_Reports_Payouts extends Presenter_Presenter
{
    use Presenter_Traits_Aff_Reports_Payouts;

    /**
     * This method will execute after controller action
     * and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        // prepare payouts
        $this->set('payouts', $this->prepare_payouts());
    }
}
