<?php

use Exceptions\Payments\NoActionNeededException;
use Fuel\Core\Input;
use GGLib\Zen\PaymentStatus;
use GGLib\Zen\WebhookIpnValidator;
use GGLib\Zen\WebhookType;
use GuzzleHttp\Psr7\ServerRequest;
use Helpers\TransactionTokenEncryptorHelper;
use Psr\Http\Message\ServerRequestInterface;

class Helpers_Payment_Zen_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
        '185.201.68.35',
    ];
    public const ALLOWED_IPS_PRODUCTION = [
        '185.201.69.16',
        '185.201.69.17',
        '185.201.69.18',
        '185.201.69.19',
        '185.201.69.20',
        '185.201.69.21',
        '185.201.69.22',
        '185.201.69.23',
    ];

    public const METHOD_NAME = Helpers_Payment_Method::ZEN_NAME;
    public const METHOD_ID = Helpers_Payment_Method::ZEN_ID;
    public const IS_CASINO_FIELD = 'is_casino';
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;
    private ServerRequestInterface $serverRequest;

    private bool $isCasino;

    protected function fetch_input_fields(): array
    {
        $this->isCasino = Input::get(self::IS_CASINO_FIELD, false);

        $this->serverRequest = new ServerRequest(Input::method(), Input::uri(), [], file_get_contents("php://input"));
        return json_decode($this->serverRequest->getBody()->getContents(), true);
    }

    protected function validate_input_fields(array $input_fields): void
    {
        $merchantIpnSecret = unserialize($this->model_whitelabel_payment_method['data'])['merchant_ipn_secret'];

        if ($this->isCasino) {
            $merchantIpnSecret = unserialize($this->model_whitelabel_payment_method['data'])['casino_merchant_ipn_secret'];
        }

        /** @var WebhookIpnValidator $webhookValidator */
        $webhookValidator = Container::make(WebhookIpnValidator::class, ['merchantIpnSecret' => $merchantIpnSecret]);
        $this->serverRequest->getBody()->rewind();
        $payload = $this->serverRequest->getBody()->getContents();
        $isRequestNotValid = !$webhookValidator->isValid($payload);

        if ($isRequestNotValid) {
            throw new Exception('The request is not valid or signature keys do not match lottery and casino.');
        }

        if ($input_fields['type'] === WebhookType::REFUND) {
            throw new NoActionNeededException('Manual refund through merchant panel has been made. This is only for informational purposes, no action is needed.');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        $encryptedTransactionToken = $input_fields['merchantTransactionId'];

        return TransactionTokenEncryptorHelper::decrypt($encryptedTransactionToken);
    }

    protected function get_amount(array $input_fields): string
    {
        return $input_fields['amount'];
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['transactionId'];
    }

    /**
     * NOTE: We fire this method after we have validated that our input field is populated with data
     */
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['status'];

        if ($status === PaymentStatus::ACCEPTED) {
            return self::TRANSACTION_SUCCESS;
        }
        $failurePaymentStatuses = [PaymentStatus::CANCELED, PaymentStatus::REJECTED];
        if (in_array($status, $failurePaymentStatuses, true)) {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING;
    }

    protected function customSuccessResponseHandle(): void
    {
        header('Content-type: application/json');
        echo json_encode(['status' => 'ok']);
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
