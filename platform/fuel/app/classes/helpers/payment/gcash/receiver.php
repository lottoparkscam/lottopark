<?php


use GGLib\Gcash\DefaultHashGenerator;
use GGLib\Gcash\DefaultWebhookValidator;
use GGLib\Gcash\OrderStatus;
use GGLib\Gcash\WebhookPayloadValidationResult;
use GGLib\Arrays\Json\JsonArrayAccessorFactory;
use Repositories\Orm\TransactionRepository;

class Helpers_Payment_Gcash_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
        '13.250.165.244',
    ];

    public const ALLOWED_IPS_PRODUCTION = [
        '13.212.55.157',
    ];

    public const METHOD_NAME = Helpers_Payment_Method::GCASH_NAME;
    public const METHOD_ID = Helpers_Payment_Method::GCASH_ID;
    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR_CODES_ARRAY = [0];
    public const TRANSACTION_PENDING = -1;

    public function create_payment(): void
    {
        exit();
    }

    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = $this->receive_transaction($transaction, $out_id, $data);

        return $ok;
    }

    /**
     * @throws Exception
     */
    protected function fetch_input_fields(): array
    {
        try {
            return json_decode(file_get_contents("php://input"), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new Exception('Invalid JSON payload.');
        }
    }

    protected function validate_input_fields(array $input_fields): void
    {
        $clientId = unserialize($this->model_whitelabel_payment_method['data'])['api_client_id'];
        $rawPayload = json_encode($input_fields);

        $hashGenerator = new DefaultHashGenerator($clientId);
        $payloadAccessorFactory = new JsonArrayAccessorFactory();

        $defaultWebhookValidator = new DefaultWebhookValidator($hashGenerator, $payloadAccessorFactory);
        $validationResult = $defaultWebhookValidator->validate($rawPayload);

        if ($validationResult->getCode() !== WebhookPayloadValidationResult::OK) {
            throw new Exception($validationResult->getMessage());
        }

        if (empty($input_fields['amount'])) {
            throw new Exception('Unable to find amount');
        }

        if (empty($input_fields['order_id'])) {
            throw new Exception('Unable to find order_id');
        }
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        $transactionRepository = Container::get(TransactionRepository::class);
        $transactionOrm = $transactionRepository->getByTransactionOutId(
            $this->whitelabel_payment_method_id,
            $input_fields['order_id']
        );

        return $transactionOrm->getOrderId();
    }

    protected function getTransactionAmount(Model_Whitelabel_Transaction $transaction): string
    {
        return ceil($transaction['amount_payment']);
    }

    protected function get_amount(array $input_fields): string
    {
        return $input_fields['amount'];
    }

    /**
     * This method saves transaction out id, based on transaction retrieved from webhook.
     * It is a special case, because this gateway does not create transaction upfront.
     * @throws Exception
     */
    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $input_fields['order_id'];
    }

    /** NOTE: We fire this method after we have validated that our input field is populated with data
     * This gateway only fires webhook on successful transactions.
     */
    protected function get_result_code(array $input_fields): int
    {
        $status = $input_fields['status'];

        if (OrderStatus::STATUS_SUCCESS === $status) {
            return self::TRANSACTION_SUCCESS;
        }

        if (OrderStatus::STATUS_CLOSED === $status) {
            return self::TRANSACTION_ERROR_CODES_ARRAY[0];
        }

        return self::TRANSACTION_PENDING;
    }
}
