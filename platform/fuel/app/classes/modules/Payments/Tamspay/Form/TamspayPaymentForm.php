<?php

namespace Modules\Payments\Tamspay\Form;

use Container;
use Exception;
use Forms_Wordpress_Payment_Base;
use Forms_Wordpress_Payment_Process;
use Fuel\Core\Fuel;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Validation;
use Fuel\Core\View;
use Helpers_General;
use Model_Whitelabel;
use Models\WhitelabelTransaction;
use Model_Whitelabel_Payment_Method;
use Model_Whitelabel_Transaction;
use Modules\Payments\PaymentUrlHelper;
use Repositories\Orm\TransactionRepository;
use Services\Shared\Logger\LoggerContract;
use Traits_Payment_Method;
use Traits_Payment_Method_Currency;
use Webmozart\Assert\Assert;

/**
 * @deprecated - ugly copy & paste
 */
final class TamspayPaymentForm extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    use Traits_Payment_Method_Currency;

    protected $payment_method = 31;
    protected string $methodSlug = 'tamspay';
    private $request = [];

    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $user_validation = null
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
    }

    public function get_whitelabel_from_whitelabel_payment_method(): ?array
    {
        if (empty($this->model_whitelabel_payment_method)) {
            $this->log_to_error_file("Empty model_whitelabel_payment_method.");

            $error_message = "No payment method of ID: " .
                $this->methodSlug;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        $whitelabel_id = (int)$this->model_whitelabel_payment_method->whitelabel_id;

        $this->whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);

        if (empty($this->whitelabel)) {
            $this->log_to_error_file("No whitelabel data found.");

            $error_message = "No whitelabel data found. Payment method of ID: " .
                $this->methodSlug;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        return $this->whitelabel;
    }

    public function process_form(): void
    {
        $this->prepareTransaction();

        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');

        $transaction = WhitelabelTransaction::find($this->transaction['id']);
        $logger->logInfo('Starting Tamspay payment', [
            'transaction' => $transaction
        ]);

        $transactionToken = $this->get_prefixed_transaction_token();
        $amount = $this->transaction->amount_payment;
        $returnUrl = Container::get(PaymentUrlHelper::class)->getSuccessUrl();
        $successConfirmationUrl = Container::get(PaymentUrlHelper::class)->getConfirmationUrlById($this->transaction->id);
        $facade = Container::getPaymentFacade($this->methodSlug);
        $whitelabelId = Container::get('whitelabel')->id;
        $config = $facade->getWhitelabelPaymentConfig($this->payment_method, $whitelabelId);
        $targetUrl = $facade->requestCheckoutUrl($transactionToken, $this->whitelabel['id'], $amount, 'KRW');
        $sid = $config['tamspay_sid'];

        $view = View::forge($config['payment_processing_view']);
        $view->set('targetUrl', $targetUrl);
        $view->set('sid', $sid);
        $view->set('amount', $amount);
        $view->set('userId', $this->transaction->whitelabel_user_id);
        $view->set('phoneNum', '123456789');
        $view->set('orderId', $transactionToken);
        $view->set('productName', 'Purchase');
        $view->set('returnUrl', $successConfirmationUrl);
        $view->set('userUrl', $returnUrl);

        $logger->logInfo('Payment request data', [
            'targetUrl' => $targetUrl,
            'sid' => $sid,
            'amount' => $amount,
            'userId' => $this->transaction->whitelabel_user_id,
            'phoneNum' => '123456789',
            'orderId' => $transactionToken,
            'productName' => 'Purchase',
            'returnUrl' => $successConfirmationUrl,
            'userUrl' => $returnUrl,
            'transaction' => $transaction
        ]);

        ob_clean();
        echo $view;
        exit();
    }

    private function getTransactionHash($id, $amount): string
    {
        return md5(sprintf('%s-%s', $id, $amount));
    }

    protected function prepareTransaction(): void
    {
        $this->transaction->set(array(
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            "additional_data" => serialize([]),
            'transaction_out_id' => $this->getTransactionHash($this->transaction->id, $this->transaction->amount)
        ));

        $this->transaction->save();
    }

    /**
     *
     * @param string $token
     * @return void
     */
    public function set_whitelabel_by_token(string $token): void
    {
        $transaction_prefix = substr($token, 0, 2);

        $whitelabels = Model_Whitelabel::find(array(
            'where' => array(
                'prefix' => $transaction_prefix,
            )
        ));

        if (!isset($whitelabels[0])) {
            $error_message = "No whitelabel row found for given token: " .
                $token;
            $this->log_error($error_message);
            exit($this->get_exit_text());
        }

        $whitelabel = $whitelabels[0]->to_array();

        $this->whitelabel = $whitelabel;
    }

    /**
     * Main method for checking result
     *
     * Return array if success
     *
     * @return array|bool
     */
    public function check_payment_result()
    {
    }

    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    public function prepare_settings_for_confirmation_all_whitelabels(
        int $whitelabel_payment_method_id
    ): void {
        $this->log_to_error_file("whitelabel_payment_method_id: " . $whitelabel_payment_method_id);

        $this->set_settings_by_whitelabel_payment_method_id($whitelabel_payment_method_id);

        $this->get_whitelabel_from_whitelabel_payment_method();
    }

    /**
     * Set whitelabel by pull from whitelabel_payment_methods table
     * by given value of $whitelabel_payment_method_id
     * So this is needed when we want to confirm payment
     * for all whitelabels
     *
     * @param int $whitelabel_payment_method_id
     * @return self
     */
    private function set_settings_by_whitelabel_payment_method_id(
        int $whitelabel_payment_method_id = null
    ): self {
        if (empty($whitelabel_payment_method_id)) {
            status_header(400);

            $this->log_error("Empty whitelabel_payment_method_id given.");
            exit($this->get_exit_text());
        }

        // Here model of whitelabel payment method will be
        // pulled based on $whitelabel_payment_method_id
        // which is given from webhook on Jeton page
        // and in fact is set as only ONE ID of the payment
        // amongs other (rest of V1 should have the same credentials)
        // and these credentials are used for the rest of process
        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $whitelabel_payment_method_id
        );

        if (empty($this->model_whitelabel_payment_method)) {
            status_header(400);

            $this->log_to_error_file("Empty model_whitelabel_payment_method.");

            $error_message = "No payment method of ID: " .
                $this->methodSlug;
            $this->log_error($error_message);

            exit($this->get_exit_text());
        }

        return $this;
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $this->process_form();
        exit();
    }

    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $urlHelper = Container::get(PaymentUrlHelper::class);
        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');
        $requestGetData = Input::get(['token', 'out']);
        $isCasino = false;

        try {
            $facade = Container::getPaymentFacade($this->methodSlug);

            if (Input::method() !== 'POST') {
                throw new Exception('Attempted to call confirm action in not expected format');
            }

            $successCode = '000000';

            $isProduction = Fuel::$env === Fuel::PRODUCTION;

            Assert::keyExists(Input::post(), 'SID', $isProduction ? 'Invalid data' : 'Missing SID');
            Assert::keyExists(Input::post(), 'TID', $isProduction ? 'Invalid data' : 'Missing TID');
            Assert::keyExists(Input::post(), 'AMOUNT', $isProduction ? 'Invalid data' : 'Missing AMOUNT');
            Assert::keyExists(Input::post(), 'USERID', $isProduction ? 'Invalid data' : 'Missing USERID');
            Assert::keyExists(Input::post(), 'ORDERID', $isProduction ? 'Invalid data' : 'Missing ORDERID');
            Assert::keyExists(Input::post(), 'REPLYCODE', $isProduction ? 'Invalid data' : 'Missing REPLYCODE');
            Assert::same(Input::post()['REPLYCODE'], $successCode, $isProduction ? 'Invalid data' : 'Replycode must be success');

            Assert::keyExists($requestGetData, 'token');
            $t = Container::get(TransactionRepository::class)->getByToken($requestGetData['token'], Container::get('whitelabel')->id);
            $isCasino = $t->isCasino ?? false;

            Assert::keyExists($requestGetData, 'out');
            Assert::same($requestGetData['out'], $this->getTransactionHash($t->id, $t->amount), 'Transaction signature incorrect.');

            $facade->confirmPayment($t->prefixed_token, $this->whitelabel['id'], $this->getRequestDataLog($t));

            Response::redirect($urlHelper->getSuccessUrl($isCasino));
        } catch (Exception $exception) {
            $logger->logErrorFromException($exception, Input::all());
            Response::redirect($urlHelper->getFailureUrl($isCasino));
        }

        return false;
    }

    protected function check_merchant_settings(): void
    {
        // TODO: Implement check_merchant_settings() method.
    }
}
