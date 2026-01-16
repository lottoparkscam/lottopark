<?php

use Fuel\Core\Input;
use GGLib\Lenco\Dto\WebhookEvent;
use GGLib\Lenco\Dto\WebhookRequest;
use GGLib\Lenco\Serialization\SerializerFactory;
use GGLib\Lenco\WebhookSignatureGenerator;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @link https://lenco-api.readme.io/reference/get-started
 */
class Helpers_Payment_Lenco_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    public const ALLOWED_IPS_STAGING = [
        '51.77.244.72',
    ];

    public const ALLOWED_IPS_PRODUCTION = [
        '*'
    ];

    public const TRANSACTION_SUCCESS = 1;
    public const TRANSACTION_ERROR = 0;
    public const TRANSACTION_PENDING = -1;

    public const METHOD_NAME = Helpers_Payment_Method::LENCO_NAME;
    public const METHOD_ID = Helpers_Payment_Method::LENCO_ID;

    private const HEADER_SIGNATURE_KEY = 'X-Lenco-Signature';

    private string $webhookSignature;
    private string $originalPayload;
    private WebhookRequest $webhookRequest;

    public function create_payment(): void
    {
        exit();
    }

    public function confirm_payment(Model_Whitelabel_Transaction &$transaction = null, string &$out_id = null, array &$data = []): bool
    {
        return $this->receive_transaction($transaction, $out_id, $data);
    }

    protected function fetch_input_fields(): array
    {
        $this->webhookSignature = Input::headers(self::HEADER_SIGNATURE_KEY, false);
        $serverRequest = new ServerRequest(
            Input::method(),
            Input::uri(),
            [self::HEADER_SIGNATURE_KEY => $this->webhookSignature],
            file_get_contents('php://input')
        );
        $deserializer = (new SerializerFactory())->createSerializer();
        $this->originalPayload = $serverRequest->getBody()->getContents();
        $this->webhookRequest = $deserializer->deserialize($this->originalPayload, WebhookRequest::class);

        if ($jsonData = json_decode($this->originalPayload, true)) {
            return $jsonData['data'];
        }

        return [];
    }

    protected function validate_input_fields(array $input_fields): void
    {
        $ipnSecretKey = unserialize($this->model_whitelabel_payment_method['data'])['api_key_secret'];

        /** @var WebhookSignatureGenerator $signatureGenerator */
        $signatureGenerator = Container::make(WebhookSignatureGenerator::class, ['secretKey' => $ipnSecretKey]);
        $generatedSignature = $signatureGenerator->generateSignature($this->originalPayload);

        if ($generatedSignature !== $this->webhookSignature) {
            throw new Exception(sprintf(
                'Invalid signature provided. WebhookSignature: (%s), GeneratedSignature: (%s)',
                $this->webhookSignature,
                $generatedSignature
            ));
        }

        $collection = $this->webhookRequest->getData();

        if (empty($collection->getAmount())) {
            throw new Exception('Unable to find amount');
        }

        if (empty($collection->getCurrency())) {
            throw new Exception('Unable to find currency');
        }

        if (empty($collection->getReference())) {
            throw new Exception('Unable to find reference (whitelabel transaction token)');
        }

        if (empty($collection->getLencoReference())) {
            throw new Exception('Unable to find lencoReference (lenco transaction id)');
        }

        if (empty($collection->getStatus())) {
            throw new Exception('Unable to find status');
        }
    }

    protected function get_result_code(array $input_fields): int
    {
        return match ($this->webhookRequest->getEvent()) {
            WebhookEvent::COLLECTION_SUCCESSFUL => self::TRANSACTION_SUCCESS,
            WebhookEvent::COLLECTION_FAILED => self::TRANSACTION_ERROR,
            default => self::TRANSACTION_PENDING,
        };
    }

    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $this->webhookRequest->getData()->getReference();
    }

    protected function get_amount(array $input_fields): string
    {
        return $this->webhookRequest->getData()->getAmount();
    }

    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $this->webhookRequest->getData()->getLencoReference();
    }

    /** Retrieve whitelabel to which transaction belongs to */
    public function getWhitelabel(): array
    {
        return $this->whitelabel;
    }
}
