<?php

use Fuel\Core\Input;
use GGLib\NowPayments\WebhookValidator;
use GGLib\NowPayments\PaymentStatus;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class Helpers_Payment_NowPayments_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const METHOD_NAME = Helpers_Payment_Method::NOWPAYMENTS_NAME;
    public const METHOD_ID = Helpers_Payment_Method::NOWPAYMENTS_ID;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;
    private ServerRequestInterface $serverRequest;
    private string $webhookSignature;
    private const HEADER_SIGNATURE_KEY = 'X-NowPayments-Sig';

    protected function fetch_input_fields(): array
    {
        $this->webhookSignature = Input::headers(self::HEADER_SIGNATURE_KEY, false); // type hint is wrong, this returns string instead of array
        $this->serverRequest = new ServerRequest(Input::method(), Input::uri(), [self::HEADER_SIGNATURE_KEY => $this->webhookSignature], file_get_contents("php://input"));
        return json_decode($this->serverRequest->getBody()->getContents(), true);
    }

    protected function validate_input_fields(array $input_fields): void
    {
        $ipnSecretKey = unserialize($this->model_whitelabel_payment_method['data'])['ipn_secret_key'];
        /** @var WebhookValidator $webhookValidator */
        $webhookValidator = Container::make(WebhookValidator::class, ['ipnSecretKey' => $ipnSecretKey]);
        $this->serverRequest->getBody()->rewind();
        $isRequestNotValid = !$webhookValidator->isValid($this->webhookSignature, $this->serverRequest->getBody()->getContents());
        if ($isRequestNotValid) {
            throw new Exception("The request is not valid and signature keys do not match. Provided: $this->webhookSignature");
        }

        if (empty($input_fields['order_id'])) {
            throw new Exception('Unable to find order_id');
        }

        if (empty($input_fields['price_amount'])) {
            throw new Exception('Unable to find price_amount');
        }

        if (empty($input_fields['payment_id'])) {
            throw new Exception('Unable to find payment_id');
        }

        if (empty($input_fields['payment_status'])) {
            throw new Exception('Unable to find payment_status');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $input_fields['order_id'];
    }

    protected function get_amount(array $input_fields): string
    {
        return (float) $input_fields['price_amount'];
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['payment_id'];
    }

    /** NOTE: We fire this method after we have validated that our input field is populated with data
     * This gateway only fires webhook on successful transactions.
     */
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['payment_status'];

        if ($status === PaymentStatus::FINISHED) {
            return self::TRANSACTION_SUCCESS;
        }
        $failurePaymentStatuses = [PaymentStatus::FAILED, PaymentStatus::REFUNDED, PaymentStatus::EXPIRED, PaymentStatus::PARTIALLY_PAID];
        if (in_array($status, $failurePaymentStatuses, true)) {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING;
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
