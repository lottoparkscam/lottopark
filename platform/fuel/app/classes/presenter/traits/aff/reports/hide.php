<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 28.12.2018
 * Time: 12:54
 */

/**
 * Trait Presenter_Traits_Aff_Reports_Commissions
 * Prepares variables, which indicates which fields should be hidden.
 */
trait Presenter_Traits_Aff_Reports_Hide
{

    /**
     * Prepare fields, which will indicate what should be hidden.
     * @return void
     */
    private function prepare_hidden_indicators(): void
    {
        $this->set('is_amount_visible', !$this->whitelabel['aff_hide_amount']);
        $this->set('is_income_visible', !$this->whitelabel['aff_hide_income']);
        $this->set('are_ticket_and_payment_cost_visible', !$this->whitelabel['aff_hide_ticket_and_payment_cost']);
        $this->set('is_lead_id_visible', !$this->user['hide_lead_id']);
        $this->set('is_transaction_id_visible', !$this->user['hide_transaction_id']);
    }
}
