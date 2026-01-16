<?php

use Fuel\Core\Response;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * @see https://sdk.picksell.eu/docs/
 */
final class PicksellSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    public const PRODUCTION_URL = 'https://sdk.picksell.eu';
    public const TESTING_URL = PicksellSender::PRODUCTION_URL; // sandbox and production is the same
    private const TRANSACTIONS_API = '/transactions';

    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null
    ) {
        parent::__construct(
            $whitelabel,
            $user,
            $transaction,
            $model_whitelabel_payment_method,
            Helpers_Payment_Method::PICKSELL_NAME,
            Helpers_Payment_Method::PICKSELL_ID
        );
    }

    /**
     * Create transaction and fetch transaction address for redirection.
     * @see https://sdk.picksell.eu/docs/#operation/TransactionController_create
     * @param array $log_data data, which will be attached to log
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        $transactionToken = $this->get_prefixed_transaction_token();

        $description = $this->get_transaction_description($transactionToken);

        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);

        $authorizationHeader = $this->generateHeaderAuthorizationFromMerchantAndToken(
            $this->payment_data['merchant_id'],
            $this->payment_data['api_key_token']
        );

        $resultUrl = $this->get_result_url();

        $fullRequestData =
            [
                'totalAmount' => strval($this->transaction['amount_payment']), // has to be STRING
                'currency' => $currencyCode, // has to be EUR
                'description' => $description,
                'callbackUrl' => $this->getConfirmationFullUrl(), // IMPORTANT - IPN url
                'returnUrl' => lotto_platform_home_url_without_language() . $resultUrl . '?token=' . $transactionToken, //  Redirected after finish the payment whatever status. Without language as Platform.php will handle language.
                'isManualPaymentSuccess' => false,
                'metadata' => [
                    'orderId' => $transactionToken,
                ]
            ];

        $createTransactionUrl = self::PRODUCTION_URL . self::TRANSACTIONS_API;
        $additionalHeaderList = [
            'Authorization' => $authorizationHeader,
        ];
        $response = $this->makeJsonRequest($createTransactionUrl, $fullRequestData, $additionalHeaderList);

        $statusCode = $response->getStatusCode();
        $isRequestSuccessful = ($statusCode >= 200) && ($statusCode < 300);
        if (!$isRequestSuccessful) {
            $this->log_error(
                "An error occurred during the attempt of " .
                "communication with transactions API of Picksell: " . $response->getReasonPhrase(),
                $fullRequestData
            );

            Session::set("message", ["error", _('Security error! Please contact us!')]);

            Response::redirect(lotto_platform_home_url('order'));
        }

        $responseBody = json_decode($response->getBody(), true);
        if (!isset($responseBody['payload']['paymentUrl'])) {
            $this->log_error(
                "An error occurred during the attempt of " .
                "communication with transactions API of Picksell and cannot find paymentUrl field.",
                $fullRequestData
            );

            Session::set("message", ["error", _('Security error! Please contact us!')]);

            Response::redirect(lotto_platform_home_url('order'));
        } else {
            $paymentUrl = $responseBody['payload']['paymentUrl'];
            $this->log_info('Transaction was created successfully.', $fullRequestData);
            return $paymentUrl;
        }

        // otherwise set general message
        throw new \Exception('General failure - unable to fetch result, reached last checkpoint');
    }

    /**
     * Basic authorization:
     * "Basic base64(merchant_id:api_key_token)"
     * @see https://sdk.picksell.eu/docs/#section/Authentication/Basic
     */
    private function generateHeaderAuthorizationFromMerchantAndToken(string $merchantId, string $apiKeyToken): string
    {
        return 'Basic ' . base64_encode($merchantId . ":" . $apiKeyToken);
    }

    /**
     * @throws Throwable when guzzle client fails
     */
    private function makeJsonRequest(string $url, array $requestData, array $additionalHeaders = []): ResponseInterface
    {
        $jsonDataHeader = ['Content-Type' => 'application/json'];
        $allHeaders = array_merge($jsonDataHeader, $additionalHeaders);

        $client = new Client(
            [
                'headers' => $allHeaders,
                'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS,
            ]
        );

        $response = $client->post(
            $url,
            ['body' => json_encode($requestData)],
        );

        return $response;
    }

    public function get_result_url(): string
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }

        $result_url = Helper_Route::ORDER_RESULT .
            Helpers_Payment_Method::PICKSELL_URI .
            "/" .
            $whitelabel_payment_method_id .
            "/";

        return $result_url;
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
}
