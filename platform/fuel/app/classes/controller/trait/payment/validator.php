<?php

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 20.03.2019
 * Time: 09:33
 */

/**
 * Use this trait to get proper data from validated payment object.
 */
trait Controller_Trait_Payment_Validator
{
    /**
     * Get proper validation for selected payment method method.
     * @param \Fuel\Core\Validation $payment_validation
     * @return \Fuel\Core\Validation|null null on undefined method
     */
    private function get_payment_method_validation(
        \Fuel\Core\Validation $payment_validation
    ): ?\Fuel\Core\Validation {
        $payment_method_id = $payment_validation->validated("input.method");

        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_for_validation();

        if (array_key_exists($payment_method_id, $list_of_payment_methods_classes)) {
            $special_validation_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_validation_special();

            if (array_key_exists($payment_method_id, $special_validation_classes)) {
                $payment_method_class = $special_validation_classes[$payment_method_id];
                return $payment_method_class::validation();
            }

            $payment_method_class = new $list_of_payment_methods_classes[$payment_method_id]();
            return $payment_method_class->validate_form();
        }

        return null;
    }

    /**
     *
     * @param \Fuel\Core\Validation $payment_validation
     * @param int $currency_id
     * @return bool|null
     */
    private function is_currency_supported(
        \Fuel\Core\Validation $payment_validation,
        int $currency_id
    ): ?bool {
        $payment_method_id = $payment_validation->validated("input.method");

        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_for_check_currency_support();

        if (array_key_exists($payment_method_id, $list_of_payment_methods_classes)) {
            $payment_method_class = new $list_of_payment_methods_classes[$payment_method_id]();
            return $payment_method_class->is_currency_supported(
                $payment_method_id,
                $currency_id
            );
        }

        return null;
    }
}
