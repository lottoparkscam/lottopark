<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;
use Helpers\CountryHelper;
use Helpers\Wordpress\LanguageHelper;

/**
 * Sender (handles first party of communication) for Easy Payment Gateway payment method.
 */
final class Helpers_Payment_Easypaymentgateway_Sender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    const PRODUCTION_URL = 'https://checkout.easypaymentgateway.com';
    const TESTING_URL = 'https://checkout-stg.easypaymentgateway.com';

    /**
     * Validation for additional fields.
     *
     * @var Validation|null
     */
    private $user_validation;

    /**
     * Helpers_Payment_Easypaymentgateway constructor.
     * @param array $whitelabel
     * @param array $user
     * @param Model_Whitelabel_Transaction|null $transaction
     * @param Model_Whitelabel_Payment_Method|null $model_whitelabel_payment_method
     * @param Validation|null $user_validation
     */
    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $user_validation = null
    ) {
        $this->user_validation = $user_validation;

        parent::__construct(
            $whitelabel,
            $user,
            $transaction,
            $model_whitelabel_payment_method,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_NAME,
            Helpers_Payment_Method::EASY_PAYMENT_GATEWAY
        );
    }

    /**
     * Fetch transaction address for redirection.
     * @param array $log_data data, which will be attached to log
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        // build url
        $url = $this->payment_url . '/EPGCheckout/rest/online/tokenize';

        $transaction_token = $this->get_prefixed_transaction_token();

        $description = $this->get_transaction_description($transaction_token);

        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);

        $confirmationUrl = $this->getConfirmationFullUrl();
        $currentLanguageShortcode = LanguageHelper::getCurrentLanguageShortcode();

        // build data
        $data = http_build_query(
            [ // TODO: {Vordis 2019-05-28 13:09:27} there are too many issets, they should match nullable fields in database
                'operationType' => 'debit', // debit|card only here
                'merchantId' => $this->payment_data['merchant_id'],
                'customerId' => $this->user['token'],
                'customerEmail' => $this->user['email'],
                'amount' => $this->transaction['amount_payment'],
                'description' => $description,
                'country' => CountryHelper::isoFromIP($this->user['last_ip']) ?: Lotto_Helper::get_best_match_user_country(),
                'currency' => $currency_code,
                'addressLine1' => $this->user['address_1'] ?? '',
                'addressLine2' => $this->user['address_2'] ?? '',
                'city' => $this->user['city'] ?? '',
                'postCode' => $this->user['zip'] ?? '',
                'phone' => $this->user['telephone'] ?? '',
                'firstname' => $this->user_validation->validated("easy-payment-gateway.name"),
                'lastname' => $this->user_validation->validated("easy-payment-gateway.surname"),
                'customerCountry' => $this->user_validation->validated("easy-payment-gateway.country_code"),
                'merchantTransactionId' => $transaction_token,
                'productId' => $this->payment_data['product_id'],
                'language' => $currentLanguageShortcode,
                'dob' => $this->user['birthdate'] ?? '',
                'customerNationalId' => $this->user_validation->validated("easy-payment-gateway.national_id"),
                'paymentSolution' => $this->payment_data['payment_solution'] ?? null,

                'topLogo' => $this->payment_data['top_logo_url'],
                'subTitle' => $this->payment_data['subtitle'],

                'statusURL' => $confirmationUrl,
                'successURL' => $success_url = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS),
                'awaitingURL' => $success_url,
                'errorURL' => $error_url = lotto_platform_home_url(Helper_Route::ORDER_FAILURE),
                'cancelURL' => $error_url,
            ]
        );

        // encrypt data for integration check
        $encrypted = $this->encrypt_via_AES(
            $this->payment_data['merchant_password'],
            $data,
            $initial_vector = random_bytes(16)
        );

        $initial_vector = base64_encode($initial_vector); // format to base64, after use. // NOTE: doesn't work if encoded in utf8
        $integrity_check = hash('sha256', $data); // NOTE: doesn't work when encoded in utf8

        // we have needed data, now create final parameters (which will be sent)
        $parameters = http_build_query(
            $parameters_array = [
                'encrypted' => $encrypted,
                'integrityCheck' => $integrity_check,
                'merchantId' => $this->payment_data['merchant_id'],
            ]
        );

        //create a new cURL resource
        $curl = curl_init($url);

        // set curl options
        curl_setopt_array(
            $curl,
            [
                CURLOPT_HTTPHEADER =>
                [   // headers
                    'Content-Type: application/x-www-form-urlencoded',
                    'encryptionMode: CBC',
                    "iv: $initial_vector"
                ],
                CURLOPT_POST => count($parameters_array),
                CURLOPT_POSTFIELDS => $parameters,
                CURLOPT_RETURNTRANSFER => true, //return response, instead of outputting
                CURLOPT_TIMEOUT => 10,
            ]
        );

        //execute request
        $result = curl_exec($curl);

        //close cURL resource
        curl_close($curl);

        // prepare data for logging
        $log_data =
            [
                'url' => $url,
                'data' => $data,
                'parameters' => $parameters,
                'initial_vector' => $initial_vector,
                'result' => $result
            ];

        // check curl result
        if (!$result) {
            throw new \Exception('Curl failure - unable to send request to epg.');
        }

        // check if this is valid address (success)
        if (substr($result, 0, 8) === 'https://') {
            return $result;
        }

        // check if we can get error from epg
        $result_xml = simplexml_load_string($result);
        $attributes = $result_xml->attributes();
        if ($attributes['status'] !== 'ERROR') {
            throw new \Exception($attributes['message']);
        }

        // otherwise set general message
        throw new \Exception('General failure - unable to fetch result, reached last checkpoint');
    }

    /**
     * Encrypt via AES, using CBC mode.
     * @param string $key key it will be automatically encoded in utf8
     * @param string $data data it will be automatically encoded in utf8
     * @param string $initial_vector initial vector, should be 16 char long, random (raw) string.
     * @return string encrypted data in base64 format.
     */
    private function encrypt_via_AES(string $key, string $data, string $initial_vector): string // 18.03.2019 11:09 Vordis TODO: export at will
    {
        // encrypt data (uri query) based on key (merchant password)
        $encryptor = new \phpseclib\Crypt\AES(\phpseclib\Crypt\AES::MODE_CBC);
        $encryptor->setKey(utf8_encode($key));
        $encryptor->setIV($initial_vector);
        $encryptor->enablePadding();

        return base64_encode($encryptor->encrypt(utf8_encode($data)));
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $payment_address = $this->fetch_transaction_address();
        if ($payment_address === null) { // invalid address
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }
        Response::redirect($payment_address); // note: exit is contained here
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string $out_id
     * @param array $data
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = false;

        return $ok;
    }
}
