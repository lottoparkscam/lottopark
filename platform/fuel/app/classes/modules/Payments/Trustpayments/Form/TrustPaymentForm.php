<?php

namespace Modules\Payments\Trustpayments\Form;

use Container;
use Core\App;
use Exception;
use Forms_Wordpress_Payment_Base;
use Forms_Wordpress_Payment_Process;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Security;
use Fuel\Core\Validation;
use Fuel\Core\View;
use Helpers_General;
use Model_Whitelabel;
use Models\WhitelabelTransaction;
use Model_Whitelabel_Payment_Method;
use Model_Whitelabel_Transaction;
use Modules\Payments\PaymentUrlHelper;
use Repositories\Orm\TransactionRepository;
use Services\MailerService;
use Services\Shared\Logger\LoggerContract;
use Throwable;
use Traits_Payment_Method;
use Traits_Payment_Method_Currency;
use Webmozart\Assert\Assert;
use Wrappers\Decorators\ConfigContract;
use Services\Logs\FileLoggerService;

/**
 * @deprecated - ugly copy & paste
 */
final class TrustPaymentForm extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    use Traits_Payment_Method_Currency;

    protected $payment_method = 33;
    protected string $methodSlug = 'trustpayments';
    private $request = [];
    private TransactionRepository $transactionRepository;
    private FileLoggerService $fileLoggerService;

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
        $this->transactionRepository = Container::get(TransactionRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
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

        $facade = Container::getPaymentFacade($this->methodSlug);

        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');

        /** @var WhitelabelTransaction $transaction */
        $transaction = WhitelabelTransaction::find($this->transaction['id']);
        $logger->logInfo('Starting Trustpayments payment', [
            'transaction' => $transaction
        ]);

        global $locale;
        $paymentLocale = $locale;
        [$supportedLocales, $defaultLocale] = array_values($facade->getConfig(['supported_locales', 'default_locale']));

        if ($isLocaleNotSupported = !in_array($paymentLocale, $supportedLocales)) {
            $paymentLocale = $defaultLocale;
            $logger->logInfo("Trustpayments locale $paymentLocale not supported, processing with default one $defaultLocale");
        }

        $token = $this->get_prefixed_transaction_token();
        $amount = $this->transaction->amount_payment;
        $returnUrl = Container::get(PaymentUrlHelper::class)->getSuccessUrl();
        $ipnConfirmUrl = Container::get(PaymentUrlHelper::class)->getConfirmationUrlById($this->transaction->id);
        $facade = Container::getPaymentFacade($this->methodSlug);
        $whitelabelId = Container::get('whitelabel')->id;
        $config = $facade->getWhitelabelPaymentConfig($this->payment_method, $whitelabelId);
        $currency = $this->get_payment_currency($this->transaction->payment_currency_id);
        $targetUrl = $facade->requestCheckoutUrl($token, $this->whitelabel['id'], $amount, $currency);
        $siteReference = $config['trustpayments_sitereference'];

        $view = View::forge($config['payment_processing_view']);
        $view->set('user', $this->user);
        $view->set('locale', $paymentLocale);
        $view->set('targetUrl', $targetUrl);
        $view->set('sitereference', $siteReference);
        $view->set('mainamount', $amount);
        $view->set('ipnConfirmUrl', $ipnConfirmUrl);
        $view->set('currencyiso3a', $currency);
        $view->set('returnUrl', $returnUrl);

        $requestData = [
            'user_email' => $this->user['email'],
            'user_login' => $this->user['login'],
            'locale' => $paymentLocale,
            'targetUrl' => $targetUrl,
            'sitereference' => $siteReference,
            'mainamount' => $amount,
            'ipnConfirmUrl' => $ipnConfirmUrl,
            'currencyiso3a' => $currency,
            'returnUrl' => $returnUrl,
            'transaction' => $transaction
        ];
        $logger->logInfo('Payment request data', $requestData);

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
        $fuelConfig = Container::get(ConfigContract::class);
        Security::check_token($fuelConfig->get('security.csrf_token_key'));
        $post = Input::post();
        $tokenKey = $fuelConfig->get('security.csrf_token_key');

        $isValid = isset($post[$tokenKey]) && Security::check_token($post[$tokenKey]);

        if (!$isValid) {
            status_header(400);

            /** @var LoggerContract $logger */
            $logger = Container::get('payments.logger');
            $logger->logWarning("Invalid CSRF token in {$this->methodSlug} payment method", $this->transaction->to_array());

            exit;
        }

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
        $app = Container::get(App::class);
        $urlHelper = Container::get(PaymentUrlHelper::class);
        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');
        $requestGetData = Input::get(['token', 'out']);
        $postData = Input::post();
        $isCasino = false;

        try {
            $facade = Container::getPaymentFacade($this->methodSlug);

            if (Input::method() !== 'POST') {
                throw new Exception('Attempted to call confirm action in not expected format');
            }

            Assert::keyExists($postData, 'transactionreference', 'Missing transactionreference');
            Assert::keyExists($postData, 'requestreference', 'Missing requestreference');
            Assert::keyExists($postData, 'orderreference', 'Missing orderreference');
            Assert::keyExists($postData, 'sitereference', 'Missing sitereference');
            Assert::keyExists($postData, 'errorcode', 'Missing errorcode');
            Assert::keyExists($postData, 'settlestatus', 'Missing settlestatus');

            Assert::keyExists($requestGetData, 'token');
            $t = Container::get(TransactionRepository::class)->getByToken($requestGetData['token'], Container::get('whitelabel')->id);
            $isCasino = $t->isCasino ?? false;

            Assert::keyExists($requestGetData, 'out');
            Assert::same($requestGetData['out'], $this->getTransactionHash($t->id, $t->amount), 'Transaction signature incorrect.');

            # @see https://help.trustpayments.com/hc/en-us/articles/4402724045073 Settle status
            $successSettleStatuses = ['0', '1', '10', '100'];
            $isError = (int)$postData['errorcode'] !== 0;
            $successfullySettled = in_array($postData['settlestatus'], $successSettleStatuses);

            // It means, business needs to approve this payment manually in merchant account
            $settleIsSuspended = (int)$postData['settlestatus'] === 2;
            if ($settleIsSuspended) {
                $errorMessage = "New status was received from IPN. Settle was suspended! Business needs to accept it manually";
                $logger->logInfo($errorMessage, [
                    'transaction' => $t,
                    'all' => Input::all()
                ]);
                $this->fileLoggerService->info(
                    "{$errorMessage} Transaction token: {$t->token} Whitelabel id: {$t->whitelabel_id}"
                );

                if ($app->isProduction()) {
                    try {
                        $mailerService = Container::get(MailerService::class);
                        $mailerService->send(
                            'peter@whitelotto.com',
                            'Transaction settle is suspended',
                            "Accept this transaction manually in Trustpayment merchant account \n\r
                    Transaction token: {$t->prefixed_token}, Whitelabel id: {$t->whitelabel_id}"
                        );
                    } catch (Throwable) {
                        $this->fileLoggerService->error(
                            "Cannot send email. Suspended transaction token: {$t->token} Whitelabel id: {$t->whitelabel_id}"
                        );
                    }
                }
            }

            $transactionIsNotApproved = $t->status !== Helpers_General::STATUS_TRANSACTION_APPROVED;

            if ($successfullySettled && !$isError && $transactionIsNotApproved) {
                $facade->confirmPayment($requestGetData['token'], $this->whitelabel['id'], $this->getRequestDataLog($t));
            }

            // This status cannot be updated to 2 again or any other status
            $settleIsCancelled = (int)$postData['settlestatus'] === 3;
            if ($settleIsCancelled) {
                $errorMessage = "Cancellation was received from IPN. Settle was canceled!";
                $logger->logWarning($errorMessage, [
                    'transaction' => $t,
                    'all' => Input::all()
                ]);
            }

            if ($isError) {
                $logger->logError('Error was received from IPN', [
                    'transaction' => $t,
                    'all' => Input::all()
                ]);
            }

            $paymentFailed = $isError || $settleIsCancelled;
            if ($paymentFailed && $transactionIsNotApproved) {
                $this->addAdditionalDataToTransaction($t, Input::all());
                $t->status = Helpers_General::STATUS_TRANSACTION_ERROR;
                $this->transactionRepository->save($t);
            }

            $isOtherIPNRequest = !$successfullySettled && !$paymentFailed;
            if ($isOtherIPNRequest) {
                $logger->logInfo('Some request from IPN received', [
                    'transaction' => $t,
                    'all' => Input::all()
                ]);
            }

            status_header(200);
            exit;
        } catch (Exception $exception) {
            $logger->logErrorFromException($exception, $postData);
            Response::redirect($urlHelper->getFailureUrl($isCasino));
        }

        return false;
    }

    /**
     * @param WhitelabelTransaction $transaction
     * @param array $data
     * @throws \Exception
     */
    private function addAdditionalDataToTransaction(WhitelabelTransaction $transaction, array $data): void
    {
        foreach ($data as $key => $value) {
            $transaction->setAdditionalData($key, $value);
        }
    }

    protected function check_merchant_settings(): void
    {
        // TODO: Implement check_merchant_settings() method.
    }
}
