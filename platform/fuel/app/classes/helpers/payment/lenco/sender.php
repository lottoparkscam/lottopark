<?php

use Fuel\Core\View;
use GGLib\Lenco\Dto\CheckoutParameters;
use GGLib\Lenco\CheckoutParametersConverter;
use GGLib\Lenco\CheckoutScriptUriProvider;
use Http\Factory\Guzzle\UriFactory;

/**
 * @link https://lenco-api.readme.io/reference/get-started
 */
final class LencoSender extends Helpers_Payment_Sender implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    protected const PRODUCTION_URL = ''; // empty as abstract parent requires it, here the payment package handles URLs
    protected const TESTING_URL = ''; // empty as abstract parent requires it, here the payment package handles URLs

    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $userFormValidation = null,
    ) {
        parent::__construct(
            $whitelabel,
            $user,
            $transaction,
            $model_whitelabel_payment_method,
            Helpers_Payment_Method::LENCO_NAME,
            Helpers_Payment_Method::LENCO_ID
        );
    }

    public function create_payment(): void
    {
        $this->update_transaction();

        $isLive = isset($this->payment_data['is_test']) ? !$this->payment_data['is_test'] : true;

        // Getting JS script address
        $scriptUri = (new CheckoutScriptUriProvider(new UriFactory(), $isLive))->getUri();

        // Converting parameters to array that could be injected into JS
        $converter = new CheckoutParametersConverter();
        $params = $converter->toArray($this->createParameters());

        $successUrl = lotto_platform_home_url(Helper_Route::ORDER_SUCCESS);
        $failureUrl = lotto_platform_home_url(Helper_Route::ORDER_FAILURE);

        $transactionToken = $this->get_prefixed_transaction_token();
        $viewData = [
            'transactionToken' => $transactionToken,
            'scriptUri' => $scriptUri,
            'params' => $params,
            'verifyUri' => $successUrl, // webhook url is set in merchant account
            'successUri' => $successUrl,
            'failureUri' => $failureUrl,
        ];

        $lencoView = View::forge('wordpress/payment/lenco');
        $lencoView->set('viewData', $viewData);

        $this->log(
            'Redirecting to Lenco checkout',
            Helpers_General::TYPE_INFO,
            $viewData
        );

        echo $lencoView;
    }

    public function confirm_payment(Model_Whitelabel_Transaction &$transaction = null, string &$out_id = null, array &$data = []): bool
    {
        return false;
    }

    protected function implementation_fetch_transaction_address(array &$log_data): string
    {
       return '';
    }

    private function createParameters(): CheckoutParameters
    {
        $transactionToken = $this->get_prefixed_transaction_token();
        $currencyCode = $this->get_payment_currency($this->transaction->payment_currency_id);

        return new CheckoutParameters(
            $this->payment_data['api_pub_key'],
            $this->user['email'] ?? '',
            $transactionToken,
            (float)$this->transaction['amount_payment'],
            $currencyCode
        );
    }
}
