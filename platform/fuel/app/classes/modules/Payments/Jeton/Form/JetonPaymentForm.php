<?php

namespace Modules\Payments\Jeton\Form;

use Container;
use Exception;
use Forms_Wordpress_Payment_Base;
use Forms_Wordpress_Payment_Process;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\Validation;
use Helpers_General;
use Model_Whitelabel;
use Models\WhitelabelTransaction;
use Model_Whitelabel_Payment_Method;
use Model_Whitelabel_Transaction;
use Modules\Payments\PaymentStatus;
use Modules\Payments\PaymentUrlHelper;
use Ramsey\Uuid\Uuid;
use Repositories\Orm\TransactionRepository;
use Services\Shared\Logger\LoggerContract;
use Services\Shared\System;
use Throwable;
use Traits_Payment_Method;
use Traits_Payment_Method_Currency;
use Webmozart\Assert\Assert;

/**
 * @deprecated - ugly copy & paste
 */
final class JetonPaymentForm extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    use Traits_Payment_Method_Currency;

    protected $payment_method = 30;
    protected string $methodSlug = 'jeton';
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
        $logger->logInfo('Starting Jeton payment', [
            'transaction' => $transaction
        ]);

        $facade = Container::getPaymentFacade($this->methodSlug);
        $system = Container::get(System::class);

        $amount = $this->transaction->amount_payment;
        $token = $this->get_prefixed_transaction_token();
        $currency = $this->get_payment_currency($this->transaction->payment_currency_id);

        try {
            $url = $facade->requestCheckoutUrl($token, $this->whitelabel['id'], $amount, $currency, 'EN');
        } catch (Exception $e) {
            if ($system->is_production_env()) {
                Session::set('message', ['error', _('Please select another payment method.')]);
                Response::redirect('/');
            }

            throw $e;
        }

        Response::redirect($url);
    }

    protected function prepareTransaction(): void
    {
        $this->transaction->set(array(
            'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
            'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id'],
            "additional_data" => serialize([]),
            'transaction_out_id' => Uuid::uuid4()->toString()
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
        $urlHelper = Container::get(PaymentUrlHelper::class);
        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');
        $requestData = Input::get(['token', 'out']);
        $isCasino = false;

        try {
            Assert::keyExists($requestData, 'token');
            $t = Container::get(TransactionRepository::class)->getByToken($requestData['token'], $this->whitelabel['id']);
            $isCasino = $t->isCasino ?? false;

            Assert::keyExists($requestData, 'out');
            Assert::same($requestData['out'], $t->transaction_out_id, 'Invalid payload');

            $facade = Container::getPaymentFacade($this->methodSlug);
            $wasSuccessful = $facade->getPaymentStatus($requestData['token'], $this->whitelabel['id'])->equals(PaymentStatus::PAID());

            if ($wasSuccessful) {
                $facade->confirmPayment($t->prefixed_token, $this->whitelabel['id'], $this->getRequestDataLog($t));
                Response::redirect($urlHelper->getSuccessUrl($isCasino));
            }
        } catch (Throwable $exception) {
            $logger->logErrorFromException($exception);
            Response::redirect($urlHelper->getFailureUrl($isCasino));
        }

        return false;
    }

    protected function check_merchant_settings(): void
    {
        // TODO: Implement check_merchant_settings() method.
    }
}
