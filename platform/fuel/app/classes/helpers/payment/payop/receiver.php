<?php

use Fuel\Core\Input;

final class Helpers_Payment_PayOp_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
    ];
    const ALLOWED_IPS_PRODUCTION = [
        '35.158.36.143',
        '18.199.249.46',
        '3.125.109.58',
        '3.127.103.117',
        '18.143.40.196',
        '54.179.10.165',
    ];
    const METHOD_NAME = Helpers_Payment_Method::PAYOP_NAME;
    const METHOD_ID = Helpers_Payment_Method::PAYOP;
    /** https://github.com/Payop/payop-api-doc/blob/master/Invoice/getInvoice.md */
    const TRANSACTION_SUCCESS = 2;
    const TRANSACTION_IS_PENDING = 4; // TODO: {Vordis 2021-07-31 14:37:21} those definitions should be done better
    const TRANSACTION_ERROR_CODES_ARRAY = [3,5];

    protected function fetch_input_fields(): array
    {
        return Input::json();
    }

    protected function validate_input_fields(array $input_fields): void { // TODO: {Vordis 2021-07-31 14:39:25} low:could use inbuilt validation instead of custom checks
        // NOTE: with auto error handling we don't need to check much - it will terminate the process if field doesn't exist. As for corrupted entries we could check, but maybe later when we overhaul payment objects and automatize the process.
        if (empty($input_fields['invoice'])) {
            throw new \Exception('Unable to find invoice');
        }
        if (empty($input_fields['transaction'])) {
            throw new \Exception('Unable to find transaction');
        }
     }

    /** https://github.com/Payop/payop-api-doc/blob/master/Checkout/ipn.md */
    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['transaction']['order']['id'];
    }

    protected function get_amount(array $input_fields): string
    {
        return $input_fields['invoice']['metadata']['amount'];
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['invoice']['txid'];
    }

    protected function get_result_code(array $input_fields): int // TODO: {Vordis 2021-07-31 14:33:09} bad architecture - should be mandatory
    {
        return $input_fields['transaction']['state'];
    }
    

    // TODO: {Vordis 2021-07-31 14:48:33} methods below shouldn't exist. It is in need of overhaul
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        
        $ok = $this->receive_transaction($transaction, $out_id, $data);
        
        return $ok;
    }

    public function create_payment(): void
    {
        exit();
    }
}
