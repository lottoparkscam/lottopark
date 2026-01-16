<?php

use Fuel\Core\Response;
use GGLib\NowPayments\Entity\CreateInvoiceRequest;
use GGLib\NowPayments\JmsSerializerFactory;
use GGLib\NowPayments\PsrClientGateway;

/**
 * @see https://gitlab.gginternational.work/gglib/now-payments
 * @see https://documenter.getpostman.com/view/7907941/S1a32n38?version=latest
 */
final class NowPaymentsSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    protected const PRODUCTION_URL = ''; // empty as abstract parent requires it, here the payment package handles URLs
    protected const TESTING_URL = '';
    public const CLIENT_TIMEOUT_IN_SECONDS = 10;

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
            Helpers_Payment_Method::NOWPAYMENTS_NAME,
            Helpers_Payment_Method::NOWPAYMENTS_ID
        );
    }

    /**
     * Create transaction and fetch transaction address for redirection.
     * @return string on success next step address, null on failure
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
        $transactionToken = $this->get_prefixed_transaction_token();
        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);

        $createInvoiceRequest = new CreateInvoiceRequest($this->transaction['amount_payment'], $currencyCode);
        $createInvoiceRequest
            ->setOrderId($transactionToken)
            ->setIpnCallbackUrl($this->getConfirmationFullUrl())
            ->setSuccessUrl(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS))
            ->setCancelUrl(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));

        $forcePaymentCurrency = $this->payment_data['force_payment_currency'];
        if (!empty($forcePaymentCurrency)) {
            $createInvoiceRequest->setPayCurrency($forcePaymentCurrency);
        }

        /** @var PsrClientGateway $gateway */
        $gateway = Container::make(PsrClientGateway::class, ['testMode' => $this->payment_data['is_test']]);
        $createInvoiceResponse = $gateway->createInvoice($this->payment_data['api_key'], $createInvoiceRequest);

        $serializer = (new JmsSerializerFactory())->createSerializer();
        $this->log_info('Transaction was created successfully.', json_decode($serializer->serialize($createInvoiceRequest), true));
        return $createInvoiceResponse->getInvoiceUrl();
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
