<?php

use Services\PaymentService;
use Models\Whitelabel;
use Fuel\Core\Validation;

/**
 * Parent of payment helpers.
 * This class should contain all basic and repeatable functionality
 * !!! Any change here, can potentially break a payment integration that extends this class
 */
abstract class Helpers_Payment_Sender
{
    use Traits_Payment_Method,
        Helpers_Payment_Trait_Log;

    /**
     *
     * @var array
     */
    protected $user = [];

    /**
     * Payment method
     * @var Model_Whitelabel_Payment_Method
     */
    protected $model_whitelabel_payment_method = [];

    /**
     * Payment data, extracted from payment method
     * @var array
     */
    protected $payment_data = [];

    /**
     * @var bool true if payment is done on testing environment.
     */
    protected $is_testing = false;

    /**
     * @var string
     */
    protected $payment_url = null;

    protected PaymentService $paymentService;
    protected PaymentMethodService $paymentMethodService;

    /**
     * Helpers_Payment_Processor constructor.
     * @param array $whitelabel
     * @param array $user
     * @param Model_Whitelabel_Transaction|null $transaction
     * @param Model_Whitelabel_Payment_Method|null $model_whitelabel_payment_method
     * @param string $name
     * @param int $method
     */
    protected function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?string $name = "",
        ?int $method = Helpers_Payment_Method::TEST
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
        $this->payment_data = unserialize($model_whitelabel_payment_method['data']); // payment credentials are stored in payment_params as extra serialized data
        $this->name = $name;
        $this->method = $method;
        $this->is_testing = (bool) ($this->payment_data["is_test"] ?? false);
        $this->payment_url = $this->is_testing ? static::TESTING_URL : static::PRODUCTION_URL; // 15.03.2019 13:42 Vordis TODO: maybe better approach in future? I cannot make abstract constants
        /** @var PaymentService $paymentService */
        $this->paymentService = Container::get(PaymentService::class);
        $this->paymentService->configure(Helpers_Payment_Method::getMethodTypeOtherUriById($method), $this->get_whitelabel_payment_method_id());
        $this->paymentMethodService = Container::get(PaymentMethodService::class);
        $this->paymentMethodService->setWhitelabel(new Whitelabel($whitelabel));
    }

    /**
     * Get human readable description of transaction.
     * @param string $token
     * @return string
     */
    protected function get_transaction_description(string $token): string
    {
        return sprintf(_("Transaction %s"), $token);
    }

    /**
     * Fetch transaction address for redirection.
     * @param array $log_data data, which will be attached to log
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    abstract protected function implementation_fetch_transaction_address(array &$log_data): string;

    /**
     * Fetch transaction address for redirection.
     * @return string|null on success next step address, null on failure
     */
    public function fetch_transaction_address(): ?string
    {   // TODO: {Vordis 2019-05-24 13:12:33} this function could be reforged into factory, but it's not mandatory.
        // first update transaction in database, and if not successful abort operation.
        if (!$this->update_transaction()) {
            $this->log_error('Failed to update transaction in database!');
            return null;
        }

        $this->setSessionTransactionIdForPageRedirectAfterPayment();

        try { // return redirection address with automatic logging.
            $log_data = [];
            $result = $this->implementation_fetch_transaction_address($log_data);
            $this->log("Redirecting to $result", Helpers_General::TYPE_INFO, $log_data);
            return $result;
        } catch (\Throwable $throwable) {
            $this->log_error($throwable->getMessage(), $log_data); // log data can be empty if there was error before request completion.
            return null;
        }
    }

    /**
     * Should be called before fetching transaction.
     * @return bool true if transaction was successfully updated.
     */
    public function update_transaction(): bool
    {
        try {
            $this->transaction
                ->set(
                    [
                        'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                        'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
                    ]
                )
                ->save();

            return true;
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * After a transaction, whether success or failure it sets transaction id to the Session
     */
    protected function setSessionTransactionIdForPageRedirectAfterPayment(): void
    {
        Session::set('transaction', $this->transaction['id']);
    }

    /**
     * Use this function to get correct URL for confirmation/IPN url
     * Example: https://lottopark.com
     */
    protected function getConfirmationBaseUrl(): string
    {
        return $this->paymentService->getPaymentConfirmationBaseUrl();
    }

    /**
     * Use this function to get correct FULL URL for confirmation/IPN url
     * Example: https://lottopark.com/order/confirm/wonderlandpay/3/
     * @throws Exception when confirmation url cannot be generated (missing payment method information)
     * Do not catch exceptions here - error handler above will terminate payment
     */
    protected function getConfirmationFullUrl(): string
    {
        return $this->paymentService->getPaymentConfirmationFullUrl();
    }

    /**
     * Use this function to get full result url - an url where user will be redirected from gateway after finishing payment
     * Example: https://lottopark.loc/order/result/pspgate/430/?token=LPD207791039
     * @throws Exception when url cannot be generated (missing payment method information)
     * Do not catch exceptions here - error handler above will terminate payment
     */
    protected function getResultFullUrlWithTransactionToken(): string
    {
        return $this->paymentService->getPaymentResultFullUrl($this->get_prefixed_transaction_token());
    }

    protected function configureUserFormValidation(Validation $validation): void
    {
        $this->paymentMethodService->configureUserFormValidation(
            $this->method,
            $validation
        );
    }

    protected function saveFormUserDetailsToCookie(): void
    {
        if ($this->paymentMethodService->isUserFormValidationConfigured()) {
            $this->paymentMethodService->saveFormUserDetailsToCookie();
        }
    }
}
