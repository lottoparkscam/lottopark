<?php

use Fuel\Core\Input;
use Services\Logs\FileLoggerService;

class Helpers_Payment_PspGate_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
        '18.159.179.155',
        '3.70.162.10',
    ];
    public const ALLOWED_IPS_PRODUCTION = [
        '18.156.29.245',
        '3.127.254.214',
    ];
    public const METHOD_NAME = Helpers_Payment_Method::PSPGATE_NAME;
    public const METHOD_ID = Helpers_Payment_Method::PSPGATE_ID;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;

    protected function fetch_input_fields(): array
    {
        return Input::json();
    }

    protected function validate_input_fields(array $input_fields): void
    {
        /** This payment gateway returns error code only when something goes terribly wrong (e.g. IP address not whitelisted).
         * When payment was declined there is no error code.
         * Log this error to slack, as it might be configuration issue with the gateway.
         */
        if (!empty($input_fields['code']) && $input_fields['code'] >= 400) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $errorMessage = 'Possible configuration error. No error message has been provided by payment gateway.';
            if (!empty($input_fields['status_description'])) {
                $errorMessage = 'Possible configuration error: ' . $input_fields['status_description'];
            }

            $fileLoggerService->error($errorMessage);
            throw new Exception($errorMessage);
        }

        // NOTE: with auto error handling it will terminate the process if field doesn't exist
        if (empty($input_fields['hash'])) {
            throw new Exception('Unable to find hash for verifying authenticity');
        }

        if (empty($input_fields['order_id'])) {
            throw new Exception('Unable to find transaction token number');
        }

        $isAuthentic = $this->isPaymentDataIntegrityCorrect(
            $input_fields['order_id'],
            unserialize($this->model_whitelabel_payment_method['data'])['api_password'],
            $input_fields['hash']
        );

        if (!$isAuthentic) {
            throw new Exception('Parameters do not match signature, possible hack attempt');
        }

        if (empty($input_fields['status'])) {
            throw new Exception('Unable to find order status');
        }

        if (empty($input_fields['amount'])) {
            throw new Exception('Unable to find order amount');
        }

        if (empty($input_fields['transaction_id'])) {
            throw new Exception('Unable to find gateway transaction id');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['order_id'];
    }

    protected function get_amount(array $input_fields): string
    {
        // Gateway operates on cents. 1 EUR = 100
        return (float) $input_fields['amount'] / 100;
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['transaction_id'];
    }

    /** NOTE: We fire this method after we have validated that our input field is populated with data
     * The valid values are: APPROVED, CANCELLED, DECLINED, ERROR, HELD, PENDING
     */
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['status'];

        if ($status === 'APPROVED') {
            return self::TRANSACTION_SUCCESS;
        }
        if ($status === 'DECLINED') {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }
        if ($status === 'ERROR') {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }
        if ($status === 'CANCELLED') {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING; // at this point payment is still pending
    }

    private function isPaymentDataIntegrityCorrect(string $orderId, string $secretKey, string $providedHash): bool
    {
        $expectedHash = hash('sha256', $orderId . $secretKey);

        return hash_equals($providedHash, $expectedHash);
    }

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

    /** Retrieve whitelabel to which transaction belongs to */
    public function getWhitelabel(): array
    {
        return $this->whitelabel;
    }
}
