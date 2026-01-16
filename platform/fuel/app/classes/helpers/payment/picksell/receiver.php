<?php

use Fuel\Core\Input;

/** https://sdk.picksell.eu/docs/#operation/transaction-callback */
class Helpers_Payment_Picksell_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
        '35.242.142.219',
    ];
    public const ALLOWED_IPS_PRODUCTION = [
        '35.242.142.219',
    ];
    public const METHOD_NAME = Helpers_Payment_Method::PICKSELL_NAME;
    public const METHOD_ID = Helpers_Payment_Method::PICKSELL_ID;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;
    private const TIMESTAMP_HEADER_KEY = 'Picksell-Timestamp';
    private const SIGNATURE_HEADER_KEY = 'Picksell-Signature';

    private array $requestHeaders;
    private string $rawRequestBody;

    /**
     * Fetches headers, raw request body and json decoded body
     * Headers and raw request body are required to verify integrity of callback
     */
    protected function fetch_input_fields(): array
    {
        $this->requestHeaders = [
            self::TIMESTAMP_HEADER_KEY => Input::headers(self::TIMESTAMP_HEADER_KEY, false),
            self::SIGNATURE_HEADER_KEY => Input::headers(self::SIGNATURE_HEADER_KEY, false),
        ];

        $this->rawRequestBody = file_get_contents("php://input");

        return Input::json();
    }

    protected function validate_input_fields(array $input_fields): void
    {
        // NOTE: with auto error handling it will terminate the process if field doesn't exist
        if ($this->requestHeaders[self::TIMESTAMP_HEADER_KEY] === false) {
            throw new Exception('Missing header: ' . self::TIMESTAMP_HEADER_KEY);
        }

        if ($this->requestHeaders[self::SIGNATURE_HEADER_KEY] === false) {
            throw new Exception('Missing header: ' . self::SIGNATURE_HEADER_KEY);
        }

        $isAuthentic = $this->isPaymentDataIntegrityCorrect(
            unserialize($this->model_whitelabel_payment_method['data'])['api_key_secret']
        );

        if (!$isAuthentic) {
            throw new Exception('Parameters do not match signature, possible hack attempt');
        }

        if (empty($input_fields['transaction']['status'])) {
            throw new Exception('Unable to find order status');
        }

        if (empty($input_fields['transaction']['totalAmount'])) {
            throw new Exception('Unable to find order amount');
        }

        if (empty($input_fields['transaction']['metadata']['orderId'])) {
            throw new Exception('Unable to find order token number');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['transaction']['metadata']['orderId'];
    }

    protected function get_amount(array $input_fields): string
    {
        return $input_fields['transaction']['totalAmount'];
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['transaction']['id'];
    }

    // NOTE: We fire this method after we have validated that our input field is populated with data
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['transaction']['status'];

        if ($status === 'PAYMENT_SUCCESS') {
            return self::TRANSACTION_SUCCESS;
        }
        if ($status === 'PAYMENT_FAILED') {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING; // at this point payment is still pending
    }

    /**
     * Use header timestamp and raw request body to generate signature
     * Compares header signature to verify IPN was sent by gateway
     * @see https://sdk.picksell.eu/docs/#section/Authentication/Callback-verification
     */
    private function isPaymentDataIntegrityCorrect(string $secretKey): bool
    {
        $signedPayload = $this->requestHeaders[self::TIMESTAMP_HEADER_KEY] . '.' . $this->rawRequestBody;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secretKey);

        return hash_equals($this->requestHeaders[self::SIGNATURE_HEADER_KEY], $expectedSignature);
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
}
