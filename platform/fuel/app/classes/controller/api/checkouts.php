<?php

use Fuel\Core\Input;
use Fuel\Core\Request;
use Fuel\Core\Response;
use GGLib\Zen\Entity\CheckoutItem;
use GGLib\Zen\Entity\CreateCheckoutRequest;
use GGLib\Zen\Entity\CreateCheckoutResponse;
use GGLib\Zen\PsrClientGateway;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\RequestOptions;
use Helpers\TransactionTokenEncryptorHelper;
use Psr\Http\Message\ResponseInterface;
use Repositories\WhitelabelPaymentMethodRepository;
use Fuel\Core\Controller;
use GuzzleHttp\Client;
use Services\Logs\FileLoggerService;

/**
 * This is an API simulating the ZEN payment gateway for the purposes of gglotto payments through
 * the lottopark.com domain
 */
class Controller_Api_Checkouts extends Controller
{
    private const LOTTOPARK_URL = 'https://api.lottopark.com';
    private const LOTTOPARK_ID = 1;
    private const ZEN_PAYMENT_METHOD_ID = 38;
    private const ENCRYPTION_ALGORITHM = 'AES-128-ECB';
    private const AES_ENCRYPTION_KEY = '4SeVqSLHKNcb%v=E';

    private Client $guzzleClient;
    private FileLoggerService $fileLoggerService;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->guzzleClient = Container::get(Client::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->fileLoggerService->setSource('api');
        $this->whitelabelPaymentMethodRepository = Container::get(WhitelabelPaymentMethodRepository::class);
    }

    public function post_checkouts(): Response
    {
        $checkoutData = $this->getCheckoutDataFromInput();
        $paymentData = $this->getPaymentData();

        $checkoutData['merchantTransactionId'] = TransactionTokenEncryptorHelper::encrypt(
            $checkoutData['merchantTransactionId']
        );

        $createCheckoutRequest = $this->createCheckoutRequest($checkoutData, $paymentData);

        try {
            $createCheckoutResponse = $this->processCheckoutRequest($paymentData, $createCheckoutRequest);
            return $this->sendResponse(['redirectUrl' => $createCheckoutResponse->getRedirectUrl()], 200);
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    private function getCheckoutDataFromInput(): array
    {
        return [
            'merchantTransactionId' => Input::json('merchantTransactionId'),
            'amount' => Input::json('amount'),
            'currency' => Input::json('currency'),
            'items' => Input::json('items'),
            'customer' => Input::json('customer'),
            'urlSuccess' => Input::json('urlSuccess'),
            'urlFailure' => Input::json('urlFailure'),
            'customIpnUrl' => Input::json('customIpnUrl'),
        ];
    }

    private function getPaymentData(): array
    {
        return $this->whitelabelPaymentMethodRepository->getPaymentApiSettingsMethodIdAndWhitelabelId(
            self::ZEN_PAYMENT_METHOD_ID,
            self::LOTTOPARK_ID,
        );
    }

    private function createCheckoutRequest(array $checkoutData, array $paymentData): CreateCheckoutRequest
    {
        $items = array_map(function ($item) {
            return new CheckoutItem($item['name'], $item['price'], $item['quantity'], $item['lineAmountTotal']);
        }, $checkoutData['items']);

        $createCheckoutRequest = new CreateCheckoutRequest(
            $paymentData['terminal_uuid'],
            $checkoutData['amount'],
            $checkoutData['currency'],
            $checkoutData['merchantTransactionId'],
            $checkoutData['customer']['firstName'],
            $checkoutData['customer']['lastName'],
            $checkoutData['customer']['email'],
            $items
        );

        $createCheckoutRequest->setCustomIpnUrl(self::LOTTOPARK_URL . '/api/checkouts/confirm?data=' . $this->encryptUrl($checkoutData['customIpnUrl']));
        $createCheckoutRequest->setUrlSuccess(self::LOTTOPARK_URL . '/api/checkouts/success?data=' . $this->encryptUrl($checkoutData['urlSuccess']));
        $createCheckoutRequest->setUrlFailure(self::LOTTOPARK_URL . '/api/checkouts/failure?data=' . $this->encryptUrl($checkoutData['urlFailure']));

        return $createCheckoutRequest;
    }

    private function processCheckoutRequest(array $paymentData, CreateCheckoutRequest $createCheckoutRequest): CreateCheckoutResponse
    {
        $gateway = Container::make(PsrClientGateway::class, ['testMode' => $paymentData['is_test']]);

        return $gateway->createCheckout($paymentData['paywall_secret'], $createCheckoutRequest);
    }

    public function post_confirm(): Response
    {
        $payload = $this->parsePayload();
        $paymentData = $this->getPaymentData();
        $payload['merchantTransactionId'] = TransactionTokenEncryptorHelper::decrypt(
            $payload['merchantTransactionId']
        );

        $payload['hash'] = $this->generateHash($payload, $paymentData);
        $decodedConfirmUrl = $this->decryptUrl(Input::get('data'));

        try {
            $response = $this->sendConfirmationRequest($decodedConfirmUrl, $payload);
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                "Payment confirmation from the ZEN gateway could not be delivered to GGL. Please check the transactions.
                TransactionId: {$payload['transactionId']}, PaymentTransactionId: {$payload['merchantTransactionId']}, 
                Message: {$exception->getMessage()}"
            );

            return $this->sendResponse([$exception->getMessage()], $exception->getCode());
        }

        return $this->sendResponse(['status' => 'ok'], 200);
    }

    private function parsePayload(): array
    {
        $serverRequest = new ServerRequest(Input::method(), Input::uri(), [], file_get_contents('php://input'));

        return json_decode($serverRequest->getBody()->getContents(), true);
    }

    private function generateHash(array $payload, array $paymentData): string
    {
        return strtoupper(hash('sha256', implode('', [
            $payload['merchantTransactionId'],
            $payload['currency'],
            $payload['amount'],
            $payload['status'],
            $paymentData['merchant_ipn_secret'],
        ])));
    }

    private function sendConfirmationRequest(string $decodedConfirmUrl, array $payload): ResponseInterface
    {
        return $this->guzzleClient->post(
            $decodedConfirmUrl,
            [
                'timeout' => 30,
                RequestOptions::JSON => $payload
            ]
        );
    }

    public function get_success(): void
    {
        $decodedSuccessUrl = $this->decryptUrl(Input::get('data'));

        Response::redirect($decodedSuccessUrl);
    }

    public function get_failure(): void
    {
        $decodedFailureUrl = $this->decryptUrl(Input::get('data'));

        Response::redirect($decodedFailureUrl);
    }

    private function encryptUrl(string $url): string
    {
        $encrypted = openssl_encrypt($url, self::ENCRYPTION_ALGORITHM, self::AES_ENCRYPTION_KEY);

        return urlencode($encrypted);
    }

    private function decryptUrl(string $encryptedUrl): string
    {
        return openssl_decrypt($encryptedUrl, self::ENCRYPTION_ALGORITHM, self::AES_ENCRYPTION_KEY);
    }

    private function handleException(Exception $exception): Response
    {
        return $this->sendResponse(json_decode($exception->getResponse(), true), $exception->getCode());
    }

    private function sendResponse(array $data, int $httpCode): Response
    {
        return new Response(json_encode($data), $httpCode, ['Content-Type' => 'application/json']);
    }
}
