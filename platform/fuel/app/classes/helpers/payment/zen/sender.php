<?php

use Fuel\Core\Response;
use Fuel\Core\Validation;
use GGLib\Zen\Entity\CheckoutItem;
use GGLib\Zen\Entity\CreateCheckoutRequest;
use GGLib\Zen\PsrClientGateway;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Helpers\TransactionTokenEncryptorHelper;
use Helpers\UrlHelper;

/**
 * @see https://www.zen.com/developer/checkout-integration?hsLang=en
 */
final class ZenSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    protected const PRODUCTION_URL = ''; // empty as abstract parent requires it, here the payment package handles URLs
    protected const TESTING_URL = '';
    public const CLIENT_TIMEOUT_IN_SECONDS = 10;
    private const ZEN_GENERATOR_REDIRECT_URL_LINK = 'https://api.lottopark.com/api/payments/zen/generateRedirectUrl';
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
            Helpers_Payment_Method::ZEN_NAME,
            Helpers_Payment_Method::ZEN_ID
        );

        /** @see wordpress/wp-content/themes/base/box/payment/methods/zen.php
         * Data coming from custom payment form (using validator).
         */
        $this->userFormValidation = $userFormValidation;
    }

    /**
     * Create transaction and fetch transaction address for redirection.
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        $transactionToken = $this->get_prefixed_transaction_token();
        $encryptedTransactionToken = TransactionTokenEncryptorHelper::encrypt($transactionToken);
        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);
        $firstName = $this->userFormValidation->validated(Validator_Wordpress_Payments_Zen::NAME_FIELD);
        $lastName = $this->userFormValidation->validated(Validator_Wordpress_Payments_Zen::SURNAME_FIELD);
        $email = $this->user['email'];

        $isNotLottopark = (int)$this->whitelabel['id'] !== 1;
        if ($isNotLottopark || (isset($this->transaction['is_casino']) && $this->transaction['is_casino'] == '1')) {
            $guzzleClient = Container::get(Client::class);

            try {
                $payload = [
                    'whitelabelId' => $this->whitelabel['id'],
                    'transactionToken' => $transactionToken,
                    'encryptedTransactionToken' => $encryptedTransactionToken,
                    'currencyCode' => $currencyCode,
                    'customerFirstName' => $firstName,
                    'customerLastName' => $lastName,
                    'customerEmail' => $email,
                    'isCasino' => IS_CASINO,
                ];

                $response = $guzzleClient->post(
                    self::ZEN_GENERATOR_REDIRECT_URL_LINK,
                    [
                        'timeout' => Helpers_General::GUZZLE_TIMEOUT_IN_SECONDS,
                        RequestOptions::JSON => $payload
                    ]
                );
                $isSuccess = $response->getStatusCode() === 200;
                if ($isSuccess) {
                    $responseData = json_decode($response->getBody(), true);
                    $this->log_info('Transaction was created successfully.', $responseData['logMessage']);

                    return $responseData['url'];
                }
            } catch (Throwable $e) {
                $response = $e->getResponse();
                $body = $response->getBody()->getContents();
                $decodedBody = json_decode($body, true);

                if ($decodedBody) {
                    $this->setLogData($decodedBody['logMessage']);
                }

                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
            }
        }

        $paymentDescription = sprintf(_("Transaction %s"), $encryptedTransactionToken);

        $item = new CheckoutItem(
            $paymentDescription,
            $this->transaction['amount_payment'],
            1,
            $this->transaction['amount_payment'],
        );

        $terminalUuid = IS_CASINO ? $this->payment_data['casino_terminal_uuid'] : $this->payment_data['terminal_uuid'];
        $paywallSecret = IS_CASINO ? $this->payment_data['casino_paywall_secret'] : $this->payment_data['paywall_secret'];

        $createCheckoutRequest = new CreateCheckoutRequest(
            $terminalUuid,
            $this->transaction['amount_payment'],
            $currencyCode,
            $encryptedTransactionToken,
            $firstName,
            $lastName,
            $email,
            [
                $item,
            ]
        );

        $customIpnUrl = IS_CASINO ? $this->getConfirmationFullUrl() . '?' . Helpers_Payment_Zen_Receiver::IS_CASINO_FIELD . '=true' : $this->getConfirmationFullUrl();
        $createCheckoutRequest->setCustomIpnUrl($customIpnUrl);
        $createCheckoutRequest->setUrlSuccess(
            UrlHelper::removeCasinoPrefixFromAbsoluteUrl(
                lotto_platform_home_url(Helper_Route::ORDER_SUCCESS) . '?transactionToken=' . $encryptedTransactionToken
            )
        );
        $createCheckoutRequest->setUrlFailure(
            UrlHelper::removeCasinoPrefixFromAbsoluteUrl(
                lotto_platform_home_url(Helper_Route::ORDER_FAILURE) . '?transactionToken=' . $encryptedTransactionToken
            )
        );

        try {
            /** @var PsrClientGateway $gateway */
            $gateway = Container::make(PsrClientGateway::class, ['testMode' => $this->payment_data['is_test']]);
            $createCheckoutResponse = $gateway->createCheckout($paywallSecret, $createCheckoutRequest);

        } catch (Throwable $exception) {
            $this->setLogData($createCheckoutRequest->toArraySigned());

            throw $exception;
        }

        $this->log_info('Transaction was created successfully.', $createCheckoutRequest->toArraySigned());
        return $createCheckoutResponse->getRedirectUrl();
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
