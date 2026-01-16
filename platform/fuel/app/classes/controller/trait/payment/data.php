<?php

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 20.03.2019
 * Time: 09:34
 */

/**
 * Use this trait to get proper data from validated payment object.
 */
trait Controller_Trait_Payment_Data
{
    /**
     * @param \Fuel\Core\Validation $payment_validation
     * @param \Fuel\Core\Validation|null $additional_values_validation
     * @return array
     */
    private function get_payment_additional_data(
        \Fuel\Core\Validation $payment_validation,
        ?\Fuel\Core\Validation $additional_values_validation
    ): array {
        $data = [];
        // return empty array, in case validation is invalid
        // (most probably not handled payment @see validator trait)
        if ($additional_values_validation === null) {
            return $data;
        }

        $payment_method_id = $payment_validation->validated("input.method");

        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_for_validation();

        if (array_key_exists($payment_method_id, $list_of_payment_methods_classes)) {
            $payment_method = new $list_of_payment_methods_classes[$payment_method_id]();
            $data = $payment_method->get_data($additional_values_validation);
        }

        return $data;
    }
}
