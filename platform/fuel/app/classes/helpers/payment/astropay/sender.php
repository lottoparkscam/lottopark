<?php

use Fuel\Core\Validation;
use Fuel\Core\Response;
use Helpers\StringHelper;

/**
 * Astropay sender
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-05-24
 * Time: 12:08:40
 */
final class Helpers_Payment_Astropay_Sender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    const PRODUCTION_URL = Helpers_Payment_Astropay_Streamline::PRODUCTION_URL;
    const TESTING_URL = Helpers_Payment_Astropay_Streamline::TESTING_URL;

    /**
     * Validation for additional fields.
     *
     * @var Validation|null
     */
    private $user_validation;

    /**
     * Helpers_Payment_Astropay constructor.
     * @param array $whitelabel
     * @param array $user
     * @param Model_Whitelabel_Transaction|null $transaction
     * @param Model_Whitelabel_Payment_Method|null $model_whitelabel_payment_method
     * @param Validation|null $user_validation Validation for additional fields.
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
            Helpers_Payment_Method::ASTRO_PAY_NAME,
            Helpers_Payment_Method::ASTRO_PAY
        );
    }

    /**
     * @throws \Exception 
     */
    private function get_whitelabel_payment_method_id_or_fail(): int
    {
        $method_id = $this->get_whitelabel_payment_method_id();
        if ($method_id === null) {
            throw new \Exception(__CLASS__ . ' unable to find whitelabel payment method id.');
        }

        return $method_id;
    }
    
    public function get_result_url(): string
    {
        // NOTICE! URL here is without protocol and domain data!!!
        $result_url = "/order/result/" .
            Helpers_Payment_Method::ASTRO_PAY_URI .
            "/" .
            $this->get_whitelabel_payment_method_id_or_fail() .
            "/";
        
        return $result_url;
    }
    
    /**
     * Fetch transaction address for redirection.
     * @param array $log_data data, which will be attached to log
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        // TODO: {Vordis 2019-05-28 11:53:35} three lines below probably should be pushed into higher layer of abstraction. Sample is too small to be sure at this point.
        $transaction_token = $this->get_prefixed_transaction_token();
        $description = $this->get_transaction_description($transaction_token);
        $currency_code = $this->get_payment_currency($this->transaction->payment_currency_id);

        $user_name = StringHelper::implode(
            ' ',
            [
                $this->user_validation->validated("astro-pay.name"),
                $this->user_validation->validated("astro-pay.surname"),
            ]
        );
        $user_address = StringHelper::implode(' ', [$this->user['address_1'], $this->user['address_2']]);
        $birth_date = empty($this->user['birthdate']) ? ''
            : (string)(new DateTime($this->user['birthdate']))
                ->format('Ymd'); // astro pay accepts date in this format.
        // NOTE: using astro pay provided class
        $streamline = new Helpers_Payment_Astropay_Streamline(
            $this->payment_data['login'],
            $this->payment_data['password'],
            $this->payment_data['secret_key'],
            $this->is_testing
        );
        
        $confirmation_url = $this->getConfirmationFullUrl();
        
        $result_url = $this->get_result_url();
        
        $national_id = $this->user_validation->validated("astro-pay.national_id");
        
        $bank_code = $this->user_validation->validated("astro-pay.bank_code");
        
        $country = Lotto_Helper::get_best_match_user_country();
        
        $response = $streamline->new_invoice(
            $transaction_token,
            $this->transaction['amount_payment'],
            $bank_code,
            $country,
            $this->user['token'],
            $national_id,
            $user_name,
            $this->user['email'],
            [
                'x_currency' => $currency_code,
                'x_description' => $description,
                'x_bdate' => $birth_date,
                'x_address' => $user_address,
                'x_zip' => $this->user['zip'],
                'x_city' => $this->user['city'],
                'x_state' => $this->user['state'],
                'x_return' => lotto_platform_home_url_without_language() . $result_url,
                'x_confirmation' => $confirmation_url,
            ]
        );
        if ($response === false) {
            throw new \Exception('AstroPay curl error.');
        }
        $decoded_response = json_decode($response);

        // prepare data for logging
        $log_data =
            [
                'url' => $streamline->get_urls()['new_invoice'],
                'parameters' => $streamline->get_parameters(),
                'http_query' => $streamline->get_parameters_http_query(),
                'result' => $response,
                'result_json' => $decoded_response
            ];

        // throw if operation was unsuccessful
        if ((int)$decoded_response->status !== Helpers_Payment_Astropay_Streamline::SUCCESS) {
            throw new \Exception($decoded_response->desc);
        }

        // return fetched address
        return $decoded_response->link;
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
     * @return bool
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
