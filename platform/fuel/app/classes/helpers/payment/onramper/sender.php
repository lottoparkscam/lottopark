<?php

use Fuel\Core\Response;
use GGLib\Onramper\OnramperPaymentUrlFactory;
use GGLib\Onramper\UrlPaymentParameters;

/**
 * @see https://docs.onramper.com
 */
final class OnramperSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    protected const PRODUCTION_URL = ''; // empty as abstract parent requires it, here the payment package handles URLs

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
            Helpers_Payment_Method::ONRAMPER_NAME,
            Helpers_Payment_Method::ONRAMPER_ID
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

        $transactionToken = $this->get_prefixed_transaction_token();
        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);

        $paymentParameters = new UrlPaymentParameters($this->payment_data['api_key']);
        $cryptoWallets = 'BUSD_BEP20:' . Helpers_Payment_Onramper_Receiver::WALLET_ADDRESS;
        $paymentParameters
            ->setCryptoWallets($cryptoWallets)
            ->setPaymentAmountInFiat($this->transaction['amount_payment'])
            ->setPaymentCurrencyInFiat($currencyCode)
            ->setExtraParameters(['transaction' => $transactionToken]);

        $onramperPaymentUrlFactory = new OnramperPaymentUrlFactory($paymentParameters);

        $this->log_info('Transaction was created successfully.', $paymentParameters->toArray());
        return $onramperPaymentUrlFactory->getPaymentUrl();
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
