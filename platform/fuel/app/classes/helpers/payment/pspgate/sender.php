<?php

use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\Str;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

/**
 * @see https://test-merchant.pspgate.com/api-docs - need to log into merchant account
 */
final class PspGateSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    public const PRODUCTION_URL = 'https://api.pspgate.com';
    public const TESTING_URL = 'https://sandbox.pspgate.com';
    private const AUTHENTICATION_API = '/api/oauth/token';
    private const CREATE_TRANSACTION_API = '/api/hpp/purchase';
    private string $integrationBaseUrl;
    private ?Validation $userFormValidation;

    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $userFormValidation = null
    ) {
        parent::__construct(
            $whitelabel,
            $user,
            $transaction,
            $model_whitelabel_payment_method,
            Helpers_Payment_Method::PSPGATE_NAME,
            Helpers_Payment_Method::PSPGATE_ID
        );

        /** @see wordpress/wp-content/themes/base/box/payment/methods/pspgate.php
         * Data coming from custom payment form (using validator).
         */
        $this->userFormValidation = $userFormValidation;
        $this->integrationBaseUrl = $this->is_testing ? self::TESTING_URL : self::PRODUCTION_URL;
    }

    /**
     * Create transaction and fetch transaction address for redirection.
     * Payment requires First Name + Last Name + Email to be real, other data like billing address can be random
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $transactionToken = $this->get_prefixed_transaction_token();
        $description = $this->get_transaction_description($transactionToken);
        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);
        try {
            $authorizationResponse = $this->sendAuthorizationRequest($this->payment_data);
        } catch (Throwable $exception) {
            $fileLoggerService->error($exception->getMessage());
            throw new Exception($exception->getMessage());
        }
        $bearerToken = $this->getFieldFromResponse($authorizationResponse, 'access_token');
        if (empty($bearerToken)) {
            $this->handleApiFailure($authorizationResponse, 'access_token');
        }

        // certain data is required, but can be random
        $fullRequestData =
            [
                'order_id' => $transactionToken,
                'ip_address' => $this->user['last_ip'],
                'amount' => $this->paymentService->convertAmountToCents($this->transaction['amount_payment']), // integer in cents e.g. 1 EUR = 100
                'currency' => $currencyCode,
                'description' => $description,
                'customer_first_name' => $this->userFormValidation->validated(Validator_Wordpress_Payments_PspGate::NAME_FIELD),
                'customer_last_name' => $this->userFormValidation->validated(Validator_Wordpress_Payments_PspGate::SURNAME_FIELD),
                'customer_phone_code' => '00' . random_int(1, 99),
                'customer_phone' => (string) random_int(100000000, 1000000000),
                'customer_email' => $this->user['email'],
                'billing_first_name' => $this->userFormValidation->validated(Validator_Wordpress_Payments_PspGate::NAME_FIELD),
                'billing_last_name' => $this->userFormValidation->validated(Validator_Wordpress_Payments_PspGate::SURNAME_FIELD),
                'billing_street' => Str::random('alpha', 40),
                'billing_city' => Str::random('alpha', 30),
                'billing_country' => self::getRandomCountryCode(),
                'billing_post_code' => random_int(10, 99999) . '-' . random_int(10, 99999),
                'return_url' => $this->getResultFullUrlWithTransactionToken(), // Url where user gets redirected after the payment whatever status (with POST data). Without language as Platform.php will handle language.
            ];


        try {
            $transactionResponse = $this->createTransactionRequest($fullRequestData, $bearerToken);
        } catch (Throwable $exception) {
            $fileLoggerService->error($exception->getMessage());
            throw new Exception($exception->getMessage());
        }
        // (HPP) Hosted Payment Page - url where user can insert card details and pay
        $hpp_url = $this->getFieldFromResponse($transactionResponse, 'hpp_url');
        if (empty($hpp_url)) {
            $this->handleApiFailure($transactionResponse, 'hpp_url');
        }

        $transactionGatewayId = $this->getFieldFromResponse($transactionResponse, 'transaction_id');
        if (empty($transactionGatewayId)) {
            $this->handleApiFailure($transactionResponse, 'transaction_id');
        }
        /** Very important! This gateway sends IPN to global whitelotto.com/order/confirm/{wl_payment_id} url
         * We need to have save transaction ID of gateway, to later know which whitelabel initiated transaction
         * @see action_order_confirm_pspgate()
         */
        $this->transaction->set(['transaction_out_id' => $transactionGatewayId]);
        $this->transaction->save();

        $paymentUrl = $hpp_url;
        $this->log_info('Transaction was created successfully.', $fullRequestData);
        return $paymentUrl;
    }

    /**
     * Generate bearer token valid for 60 minutes
     * @throws Throwable when guzzle client fails
     */
    private function sendAuthorizationRequest(array $paymentData): ResponseInterface
    {
        $client = new Client();
        $response = $client->post($this->integrationBaseUrl . self::AUTHENTICATION_API, [
            'headers' => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            'json' => [
                "grant_type" => "password",
                "client_id" => $paymentData['client_id'],
                "client_secret" => $paymentData['client_secret'],
                "username" => $paymentData['username'],
                "password" => $paymentData['password'],
            ],
            'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS,
        ]);

        return $response;
    }

    /**
     * @throws Throwable when guzzle client fails
     */
    private function createTransactionRequest(array $requestData, string $bearerToken): ResponseInterface
    {
        $client = new Client();
        $response = $client->post($this->integrationBaseUrl . self::CREATE_TRANSACTION_API, [
            'headers' => [
                "Authorization" => "Bearer " . $bearerToken,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            'json' => $requestData,
            'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS,
        ]);

        return $response;
    }

    /**
     * Retrieves given field from response body if status ok and field exists.
     * Otherwise, returns empty string.
     */
    private function getFieldFromResponse(ResponseInterface $response, string $fieldName): string
    {
        $statusCode = $response->getStatusCode();
        $isRequestSuccessful = ($statusCode >= 200) && ($statusCode < 300);
        if (!$isRequestSuccessful) {
            return '';
        }

        $responseBody = json_decode((string) $response->getBody(), true);
        if (empty($responseBody[$fieldName])) {
            return '';
        }

        return $responseBody[$fieldName];
    }

    /**
     * Redirects user back to whitelabel if something went wrong.
     * Flow exit is contained here.
     */
    private function handleApiFailure(ResponseInterface $response, string $fieldName): void
    {
        $this->log_error(
            "An error occurred during the attempt of " .
            "communication with transactions API of PSPGATE and cannot find $fieldName field.",
            json_decode((string) $response->getBody(), true)
        );

        Session::set("message", ["error", _('Security error! Please contact us!')]);

        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
    }

    public function create_payment(): void
    {
        $payment_address = $this->fetch_transaction_address();
        if ($payment_address === null) { // invalid address
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        Response::redirect($payment_address); // note: exit is contained here
    }

    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = false;

        return $ok;
    }

    private const COUNTRY_CODES = [
        "BY",
        "BE",
        "BZ",
        "BJ",
        "BM",
        "BT",
        "BO",
        "BA",
        "EE",
        "ET",
        "FK",
        "FO",
        "FJ",
        "FI",
        "FR",
        "LT",
        "LU",
        "MM",
        "NA",
        "NR",
        "NP",
        "NL",
        "AN",
        "NC",
        "NZ",
        "NI",
        "NE",
        "NG",
        "NU",
        "NF",
        "MP",
        "NO",
        "OM",
        "PK",
        "PW",
        "PA",
        "PG",
        "PY",
    ];

    /**
     * Gateway allows passing random data. Selects random country code from constant array.
     */
    private static function getRandomCountryCode(): string
    {
        $randomArrayKey = array_rand(self::COUNTRY_CODES);
        return self::COUNTRY_CODES[$randomArrayKey];
    }
}
