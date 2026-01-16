<?php

use Fuel\Core\Input;
use GGLib\Onramper\DefaultWebhookValidator;
use GGLib\Onramper\HmacSignatureGenerator;
use GGLib\Onramper\WebhookStatus;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class Helpers_Payment_Onramper_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const METHOD_NAME = Helpers_Payment_Method::ONRAMPER_NAME;
    public const METHOD_ID = Helpers_Payment_Method::ONRAMPER_ID;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;
    private ServerRequestInterface $serverRequest;
    public const WALLET_ADDRESS = '0x51219a98d7F98d5d9aa51F56B7F77c3CD5048aCF'; // for USDT and BUSD_BEP20

    protected function fetch_input_fields(): array
    {
        $signatureHeaderKey = DefaultWebhookValidator::SIGNATURE_HEADER_KEY;
        $webhookSignature = Input::headers($signatureHeaderKey, false); // type hint is wrong, this returns string instead of array
        $this->serverRequest = new ServerRequest(Input::method(), Input::uri(), [$signatureHeaderKey => $webhookSignature], file_get_contents("php://input"));
        return json_decode($this->serverRequest->getBody()->getContents(), true);
    }

    protected function validate_input_fields(array $input_fields): void
    {
        $signatureGenerator = new HmacSignatureGenerator(unserialize($this->model_whitelabel_payment_method['data'])['api_key_secret']);
        $defaultWebhookValidator = new DefaultWebhookValidator($signatureGenerator);
        $isRequestNotValid = !$defaultWebhookValidator->isValid($this->serverRequest);
        if ($isRequestNotValid) {
            throw new Exception($defaultWebhookValidator->getMessage());
        }

        /**
         * Different providers can return different currency representation (gateway does not unify them):
         * - Moonpay can return BUSD_BSC BUSD_BEP20
         * - Itez can return BEP20BUSD
         */
        $paidInCryptocurrency = strtoupper($input_fields['payload']['outCurrency']); // this is cryptocurrency that will be transferred to us
        if (!in_array($paidInCryptocurrency, ['USDT', 'BUSD_BEP20', 'BUSD_BSC', 'BEP20BUSD'], true)) {
            throw new Exception("We did not receive correct cryptocurrency - $paidInCryptocurrency. User has modified something!");
        }

        $paidToWalletAddressField = $input_fields['payload']['wallet']; // example: "bitcoin:2N3oefVeg6stiTb5Kh3ozCSkaqmx91FDbsm" or "2N3oefVeg6stiTb5Kh3ozCSkaqmx91FDbsm"
        $isWalletAddressInColonFormat = preg_match('/:/', $paidToWalletAddressField) === 1;
        if ($isWalletAddressInColonFormat) {
            $paidToWalletAddress = explode(':', $paidToWalletAddressField)[1];
        } else {
            $paidToWalletAddress = $paidToWalletAddressField;
        }


        if ($paidToWalletAddress !== self::WALLET_ADDRESS) {
            throw new Exception("User did not pay to correct wallet address. Paid to: $paidToWalletAddress");
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        $partnerContext = json_decode($input_fields['payload']['partnerContext'], true);
        return $partnerContext['transaction'];
    }

    protected function get_amount(array $input_fields): string
    {
        // Payment currency has to match currency that user paid in - they can change currencies at gateway. We have transaction model at this point.
        $transactionPaymentCurrencyCode = Helpers_Currency::findCurrencyById($this->transaction->payment_currency_id)['code'];
        $paidInCurrencyCode = strtoupper($input_fields['payload']['inCurrency']);
        if ($paidInCurrencyCode !== $transactionPaymentCurrencyCode) {
            throw new Exception("User paid in incorrect currency $paidInCurrencyCode, where expected currency is $transactionPaymentCurrencyCode");
        }

        return (float) $input_fields['payload']['inAmount'];
    }

    /**
     * This method saves transaction out id, based on transaction retrieved from webhook.
     * It is a special case, because this gateway does not create transaction upfront.
     * @throws Exception
     */
    protected function get_transaction_outer_id(array $input_fields): string
    {
        $transactionOutId = $input_fields['payload']['txId'];
        if (empty($this->transaction->transaction_out_id)) {
            $this->transaction->transaction_out_id = $transactionOutId;
            $this->transaction->save();
        }

        return $transactionOutId;
    }

    /** NOTE: We fire this method after we have validated that our input field is populated with data
     * This gateway only fires webhook on successful transactions.
     */
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['type'];
        $webhookStatus = new WebhookStatus($status);

        if ($webhookStatus->isSuccessful()) {
            return self::TRANSACTION_SUCCESS;
        }
        if ($webhookStatus->isFailed()) {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING; // at this point payment is still pending
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
