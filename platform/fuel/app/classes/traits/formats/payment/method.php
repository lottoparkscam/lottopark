<?php

/**
 * This trait translates payment method from id to human readable.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Traits_Formats_Payment_Method
{

    /**
     * This function translates method id into human readable form.
     * @param int $method_type type of the payment method.
     * @param array|null $payment_methods payment methods array.
     * @param int|null $payment_method_id payment method id.
     * @return string human readable payment method.
     */
    public function translate_payment_method(
        int $method_type,
        array $payment_methods = null,
        int $payment_method_id = null
    ): string // TODO: maybe better way without mixing types
    {
        switch ($method_type) {
            default:
                return "unknown - possible error";
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                return "bonus balance payment";
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                return "balance payment";
            case Helpers_General::PAYMENT_TYPE_CC:
                return "credit card payment";
            case Helpers_General::PAYMENT_TYPE_OTHER:
                // $pm is safe, in case of PAYMENT_TYPE_OTHER method it cannot be null.
                return $payment_methods[$payment_method_id]['pname'];
        }
    }

    /**
     * Safely get payment method.
     * @param Model $transaction transaction model.
     * @return int|null payment method id or null on failure.
     */
    public function get_payment_method_id(Model_Whitelabel_Transaction $transaction) // TODO: maybe better way without mixing types
    {
        // note that payment method can be null, and therefore non existent in model object (methodID == 2 or 1)
        return $transaction->whitelabel_payment_method_id ?? null; // assign method if exists, null otherwise
    }
}
