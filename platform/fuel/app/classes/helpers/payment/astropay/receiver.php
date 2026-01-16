<?php

/**
 * Astropay receiver
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-05-24
 * Time: 12:08:40
 */
abstract class Helpers_Payment_Astropay_Receiver extends Helpers_Payment_Receiver
{
    /**
     * Method name.
     */
    const METHOD_NAME = Helpers_Payment_Method::ASTRO_PAY_NAME;

    /**
     * Method id.
     */
    const METHOD_ID = Helpers_Payment_Method::ASTRO_PAY;

    /**
     * Fetch input fields.
     *
     * @return array Input fields - result of Fuel\Input::method.
     */
    protected function fetch_input_fields(): array
    {
        return Input::post();
    }

    /**
     * Transaction not found.
     */
    const TRANSACTION_NOT_FOUND = 6;
    /**
     * Transaction is pending.
     */
    const TRANSACTION_IS_PENDING = 7;
    /**
     * Transaction rejected by bank.
     */
    const TRANSACTION_REJECTED_BY_BANK = 8;
    /**
     * Transaction success.
     */
    const TRANSACTION_SUCCESS = 9;

    /**
     * Names of the transaction codes.
     */
    const TRANSACTION_CODE_NAMES =
    [
        self::TRANSACTION_NOT_FOUND => 'Transaction not found',
        self::TRANSACTION_REJECTED_BY_BANK => 'Transaction rejected by bank',
    ]; // NOTE: could be achieved by reflection, but it would be overkill + this is faster.

    /**
     * Any explicit error codes that can be returned by IPN
     */
    const TRANSACTION_ERROR_CODES_ARRAY = [Helpers_Payment_Astropay_Receiver::TRANSACTION_REJECTED_BY_BANK];

    /**
     * Get result code of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return integer
     */
    protected function get_result_code(array $input_fields): int
    {
        return $input_fields['result'];
    }
    /**
     * Get inner id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['x_invoice'];
    }
    /**
     * Get amount in the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_amount(array $input_fields): string
    {
        return $input_fields['x_amount'];
    }
    /**
     * Get outer (in the payment solution) id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['x_document'];
    }

    /**
     * Build control string for received notification.
     *
     * @param array $input_fields
     * @return string
     */
    protected function build_control(array $input_fields): string
    {
        // TODO: {Vordis 2019-05-29 16:35:11} From what I see I have to fetch payment method here.
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find(
            [
                'where' => [
                    'whitelabel_id' => $this->whitelabel['id'],
                    'payment_method_id' => Helpers_Payment_Method::ASTRO_PAY,
                ]
            ]
        )[0];
        $data = unserialize($whitelabel_payment_method->data);
        $message = $data['login'] . $input_fields['result'] . $input_fields['x_amount'] . $input_fields['x_invoice'];
        return strtoupper(hash_hmac('sha256', pack('A*', $message), pack('A*', $data['secret_key'])));
    }

    /**
     * Concrete validation of received input fields done by child.
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return void
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function validate_input_fields(array $input_fields): void
    {
        // check control field (authorization)
        if ($input_fields['x_control'] !== $this->build_control($input_fields)) {
            throw new \Exception('Request control differ from ours.');
        }
    }
}
