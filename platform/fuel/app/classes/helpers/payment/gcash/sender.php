<?php

use GGLib\Gcash\PsrGcashClient;
use GGLib\Gcash\GcashClientException;
use GGLib\Gcash\Entity\CreateOrderRequest;
use Fuel\Core\Response;

/**
 * @link https://stagingapi.ops-gate.com/
 */
final class GcashSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    // empty as abstract parent requires it, here the payment package handles URLs
    protected const PRODUCTION_URL = '';
    protected const TESTING_URL = '';

    public const CLIENT_TIMEOUT_IN_SECONDS = 10;
    private PsrGcashClient $client;

    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
    ) {
        parent::__construct(
            $whitelabel,
            $user,
            $transaction,
            $model_whitelabel_payment_method,
            Helpers_Payment_Method::GCASH_NAME,
            Helpers_Payment_Method::GCASH_ID
        );
    }

    /**
     * Create transaction and fetch transaction address for redirection.
     * Payment requires First Name + Last Name + Email to be real, other data like billing address can be random
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        /**
         * Round amount up to receive matching value from the Gateway CreateOrderResponse
         */
        $gatewayPaymentAmount = (int) ceil($this->transaction['amount_payment']);

        $request = new CreateOrderRequest(
            $gatewayPaymentAmount,
            $this->payment_data['merchant_name'],
            $this->payment_data['merchant_id'],
            $this->user['id']
        );

        try {
            $response = $this->client->createOrder($request);
        } catch(GcashClientException $exception) {
            $log_data = $request->toArray();
            throw $exception;
        }

        /**
         * The gateway does not allow to add our transaction ID as parameter and then
         * receive it in the callback. We can only fetch using the 'order_id' parameter.
         */
        $this->transaction->set(['transaction_out_id' => $response->getOrderId()]);
        $this->transaction->save();

        $this->log_info('Transaction was created successfully.', $request->toArray());

        return $response->getCheckoutUrl();
    }

    public function create_payment(): void
    {
        $this->client = Container::make(PsrGcashClient::class, [
            'testMode' => $this->payment_data['is_test'],
            'apiClientId' => $this->payment_data['api_client_id'],
            'apiKeySecret' => $this->payment_data['api_key_secret'],
        ]);

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
