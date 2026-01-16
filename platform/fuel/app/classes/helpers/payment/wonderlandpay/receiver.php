<?php

use Fuel\Core\Input;

class Helpers_Payment_WonderlandPay_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
    ];
    public const ALLOWED_IPS_PRODUCTION = [
        '13.52.7.225',
        '13.56.75.91',
        '18.166.128.221',
        '18.166.132.75',
        '18.166.35.88',
    ];
    public const METHOD_NAME = Helpers_Payment_Method::WONDERLANDPAY_NAME;
    public const METHOD_ID = Helpers_Payment_Method::WONDERLANDPAY;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_IS_PENDING = -1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];

    protected function fetch_input_fields(): array
    {
        return Input::post();
    }

    protected function validate_input_fields(array $input_fields): void
    {
        // NOTE: with auto error handling we don't need to check much - it will terminate the process if field doesn't exist. As for corrupted entries we could check, but maybe later when we overhaul payment objects and automatize the process.
        $isAuthentic = $this->isPaymentDataIntegrityCorrect(
            $input_fields,
            unserialize($this->model_whitelabel_payment_method['data'])['wonderlandpay_secret_key']
        );

        if (!$isAuthentic) {
            throw new \Exception('Parameters do not match signature, possible hack attempt');
        }

        if (!is_numeric($input_fields['orderStatus'] ?? null)) {
            throw new \Exception('Unable to find order status');
        }

        if (empty($input_fields['orderNo'])) {
            throw new \Exception('Unable to find order number');
        }

        if (empty($input_fields['signInfo'])) {
            throw new \Exception('Unable to find signature');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['orderNo'];
    }

    protected function get_amount(array $input_fields): string
    {
        return $input_fields['orderAmount'];
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['tradeNo'];
    }

    protected function get_result_code(array $input_fields): int
    {
        return $input_fields['orderStatus'];
    }

    /**
     * signInfo provided by wonderlandpay is using sha256 algorithm, but is in uppercase format
     * hash() function in PHP returns signature in lowercase
     * So we use mb_strtoupper to try to get our signature the same as provided one - not other way around
     * Note mb_ is used to handle UTF8 characters
     */
    private static function isPaymentDataIntegrityCorrect(array $inputData, string $secretKey): bool
    {
        $inputDataForSignatureVerification = $inputData['merNo'] . $inputData['gatewayNo'] . $inputData['tradeNo'] . $inputData['orderNo'] . $inputData['orderCurrency'] . $inputData['orderAmount'] . $inputData['orderStatus'] . $inputData['orderInfo'] . $secretKey;
        $signatureForPassedData = hash('sha256', $inputDataForSignatureVerification);
        $isSignatureEqualCaseInsensitive = mb_strtoupper($signatureForPassedData) === $inputData['signInfo'];

        return $isSignatureEqualCaseInsensitive;
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
