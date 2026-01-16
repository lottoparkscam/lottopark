<?php

use Exceptions\Payments\NoActionNeededException;
use Repositories\Orm\TransactionRepository;

/**
 * Archetype of payment receivers.
 */
abstract class Helpers_Payment_Receiver
{
    use Traits_Payment_Method,
        Helpers_Payment_Trait_Log;

    /**
     * True if receiver should discard notifications outside of specified ip pool.
     *
     * @var bool
     */
    private $is_ip_constrained;

    /**
     * True if receiver should handle transaction as pending one.
     * NOTE: should be set by child on processing of concrete return codes.
     *
     * @var boolean
     */
    public $is_pending = false;

    /**
     * True if receiver is a return receiver.
     *
     * @var boolean
     */
    private $is_return = false;
    protected $success_message = 'Transaction succeeded';
    protected $pending_message = 'Received confirmation with pending status!';
    protected $initial_message = 'Received confirmation for the transaction';

    /**
     * List of error codes that can be returned by provider.
     * Error codes allow us to return 200 Ok that we understand transaction
     * Also to update transaction status with explicit error.
     * NOTE: Extending classes should specify these!
     */
    protected const TRANSACTION_ERROR_CODES_ARRAY = [];

    /**
     *
     * @var null|Model_Whitelabel_Payment_Method
     */
    protected $model_whitelabel_payment_method = null;

    /**
     * Helpers_Payment_Receiver constructor.
     * @param array|null $whitelabel - null when IPN comes through whitelotto.com/order/confirm/{whitelabel_payment_id}/
     */
    public function __construct(?array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
        $this->name = static::METHOD_NAME; // TODO: {Vordis 2019-05-29 14:32:47} Unsure if it's the best solution (consts vs constructor vs private fields)
        $this->method = static::METHOD_ID;

        $this->is_ip_constrained = defined('static::ALLOWED_IPS_PRODUCTION')
            && defined('static::ALLOWED_IPS_PRODUCTION');

        // check if this is return receiver via reflection
        $class = get_called_class();
        $this->is_return = substr($class, -6, 6) === 'Return'; // NOTE: to work properly file must be named return.php
        // set proper messages for return receiver.
        if ($this->is_return) {
            $this->success_message = 'Successful return of the transaction';
            $this->pending_message = 'Received return with pending status!';
            $this->initial_message = 'Received return of the transaction';
        }
    }

    /**
     * Provide method, which will fetch input fields.
     *
     * @return array Input fields - result of Fuel\Input::method.
     */
    abstract protected function fetch_input_fields(): array;
    // NOTE: below fields are done via abstract method to allow flexibility - we don't know how deep some field may be hidden
    // so string holding index to element is far from sufficient.
    /**
     * Get result code of the transaction.
     * Made into optional to allow flexibility in result check.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return integer
     */
    protected function get_result_code(array $input_fields): int
    {
        return -1;
    }
    /**
     * Get inner id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    abstract protected function get_transaction_id_from_input_fields(array $input_fields): string;

    /**
     * Get amount in the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    abstract protected function get_amount(array $input_fields): string;
    /**
     * Get outer (in the payment solution) id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    abstract protected function get_transaction_outer_id(array $input_fields): string;

    /**
     * Concrete validation of received input fields done by child.
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return void
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    abstract protected function validate_input_fields(array $input_fields): void;

    /**
     * Validate result of the transaction.
     * If payment status is explicit success then return early
     * If payment status is explicit error then throw ErrorException to mark transaction as failed
     * In any other case the payment is still pending AND we do not care what acronym for pending it is
     *
     * @param array $input_fields $input_fields input fields received from notification @see fetch_input_fields()
     * @return void
     * @throws ErrorException when resultCode matches failed transaction status
     */
    protected function validate_result(array $input_fields): void
    {
        $resultCode = $this->get_result_code($input_fields);
        $this->is_pending = false;

        $isSuccessPayment = $resultCode === static::TRANSACTION_SUCCESS;
        if ($isSuccessPayment) {
            return;
        }

        $isFailedPayment = in_array($resultCode, static::TRANSACTION_ERROR_CODES_ARRAY);
        if ($isFailedPayment) {
            throw new ErrorException("Request status is error: resultCode=$resultCode");
        }

        // The payment is pending, we do not throw error here
        $this->is_pending = true;
    }


    /**
     * Get model for whitelabel payment method
     *
     * @return \Model_Whitelabel_Payment_Method|null
     * @throws Exception
     */
    protected function get_model_whitelabel_payment_method(): ?Model_Whitelabel_Payment_Method
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();

        if (empty($whitelabel_payment_method_id)) {
            throw new Exception('No whitelabel_payment_method_id set for transaction!');
        }

        $this->model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if ($this->model_whitelabel_payment_method === null) {
            throw new Exception(
                'No model_whitelabel_payment_method found ' .
                    ' for transaction for given ID: ' .
                    $whitelabel_payment_method_id
            );
        }

        return $this->model_whitelabel_payment_method;
    }

    /**
     *
     * @return bool
     */
    protected function is_test(): bool
    {
        if (!isset($this->model_whitelabel_payment_method)) {
            throw new Exception('No model_whitelabel_payment_method found!');
        }

        $payment_data = unserialize($this->model_whitelabel_payment_method['data']);

        if (!$payment_data) {
            throw new Exception('Empty model_whitelabel_payment_method data!');
        }

        if (empty($payment_data['is_test']) || (int) $payment_data['is_test'] === 0) {
            return false;
        }

        return true;
    }

    /**
     * IP Whitelists can include specific IP addresses
     * or an asterisk (*) as a wildcard to allow all IP addresses.
     * For example:
     * public const ALLOWED_IPS_PRODUCTION = ['172.0.0.1'];
     * public const ALLOWED_IPS_PRODUCTION = ['*'];
     *
     * @throws Exception
     */
    protected function check_ip(): void
    {
        if ($this->is_ip_constrained) { // TODO: {Vordis 2019-05-29 14:31:50} I'm not sure if we should log requests outside of allowed ips.
            // check if we should allow request from such ip
            $allowed_ips = [];
            if ($this->is_test()) {
                $allowed_ips = static::ALLOWED_IPS_STAGING;
            } else {
                $allowed_ips = static::ALLOWED_IPS_PRODUCTION;
            }
            $isNotAllowedFromAll = !in_array('*', $allowed_ips);
            $isIpNotAllowed = !in_array(Lotto_Security::get_IP(), $allowed_ips, true);
            $isIpIncorrect = $isNotAllowedFromAll && $isIpNotAllowed;
            if ($isIpIncorrect) {
                throw new Exception('IP of the notification sender is not allowed.');
            }
        }
    }

    /**
     * @param null|Model_Whitelabel_Transaction $transaction reference, transaction model instance in array form.
     * @param string $out_id reference, outer identifactor of transaction.
     * @param array $data reference, data of the transaction
     * @return bool true on success.
     */
    public function receive_transaction(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {   // TODO: {Vordis 2019-05-29 16:07:56} without types due to null initialization
        $transactionPrefixedToken = "";
        $log_data = [];

        try {
            // log that confirmation was received and read input fields
            $input_fields = $this->fetch_input_fields();
            $this->log(
                $this->initial_message,
                Helpers_General::TYPE_INFO,
                $log_data = [
                    'fields' => $input_fields,
                    'fields_raw' => file_get_contents("php://input"),
                ]
            );

            // input basic integrity check
            // I moved that here because in my opinion
            // there if input is empty the code should not run further
            if (empty($input_fields)) {
                throw new LogicException('Input fields are empty!');
            }

            $this->get_model_whitelabel_payment_method();

            $this->check_ip();

            // execute child logic (concrete validation of the fields and transaction)
            $this->validate_input_fields($input_fields);

            $transactionPrefixedToken = $this->get_transaction_id_from_input_fields($input_fields); // get for error logs.
            $isTransactionTokenNotCorrect = preg_match('/^[A-Z]{3}[0-9]+$/', $transactionPrefixedToken) !== 1;
            if ($isTransactionTokenNotCorrect) {
                throw new LogicException('Transaction token is incorrect');
            }

            // fetch transaction
            $transactionRepository = Container::get(TransactionRepository::class);
            if (!empty($this->whitelabel)) {
                // LPP -> LottoPark Purchase
                // LPD -> LottoPark Deposit
                $isZenPaymentFromOtherWhitelabelThanLottopark = $this->method === Helpers_Payment_Method::ZEN_ID &&
                    (!str_starts_with($transactionPrefixedToken, 'LPD') || !str_starts_with($transactionPrefixedToken, 'LPP'));
                if ($isZenPaymentFromOtherWhitelabelThanLottopark) {
                    $transactionOrm = $transactionRepository->getByPrefixedToken($transactionPrefixedToken);
                    $this->whitelabel = $transactionOrm->whitelabel->to_array();
                    $this->transaction = Model_Whitelabel_Transaction::fromOrm($transactionOrm);
                } else {
                    $this->transaction = Model_Whitelabel_Transaction::get_transaction_for_prefixed_token(
                        $transactionPrefixedToken,
                        (int)$this->whitelabel['id']
                    );
                }
            } else {
                // The transaction arrived through whitelotto.com/order/confirm/id
                $transactionOrm = $transactionRepository->getByPrefixedToken($transactionPrefixedToken);
                $this->whitelabel = $transactionOrm->whitelabel->to_array();
                $this->transaction = Model_Whitelabel_Transaction::fromOrm($transactionOrm);
            }

            // check if transaction was found
            if ($this->transaction === null) {
                throw new LogicException('Transaction model not found');
            }

            $this->validate_result($input_fields);

            // check transaction amount_payment
            $transactionAmount = sprintf('%3.2f', $this->getTransactionAmount($this->transaction));
            $gatewayTransactionAmount = sprintf('%3.2f', $this->get_amount($input_fields));
            if ($transactionAmount !== $gatewayTransactionAmount) {
                throw new LogicException('Amount doesn\'t match');
            }

            // everything seems fine - set out_id (id of transaction in payment solution), data (data attached by payment solution) and transaction
            $out_id = $this->get_transaction_outer_id($input_fields);
            $transaction = $this->transaction;
            $data = [
                'result' => $input_fields
            ];

            // log success or info for pending
            if ($this->is_pending) {
                $this->log_info($this->pending_message, $log_data);
            } else {
                $this->log_success($this->success_message, $log_data);
            }

            $this->customSuccessResponseHandle();

            // for normal receiver only success should return true.
            return !$this->is_pending;
        } catch (LogicException $exception) {
            /* NOTE: these here are hard errors:
             * - Input fields are empty!
             * - Transaction model not found
             * - Amount doesn't match
             * Return 400 and do not update status
             */
            status_header(400, 'Unable to process transaction.');
            $this->log_error($exception->getMessage() . ', transaction: ' . $transactionPrefixedToken ?? '', $log_data);
            $this->updateTransactionWithResultAndStatusIfValid($exception->getMessage());

            return false;
        } catch (ErrorException $exception) {
            // Here we understand the request, IPN notified us with explicit error so update transaction status
            status_header(200, 'Ok');
            $this->customSuccessResponseHandle();
            $this->log_error($exception->getMessage() . ', transaction: ' . $transactionPrefixedToken ?? '', $log_data);
            $this->updateTransactionWithResultAndStatusIfValid($exception->getMessage(), true);

            return false;
        } catch (NoActionNeededException $exception) {
             /**
              * This exception type can be used, when info log is only needed.
              * For example, when refund webhook arrives and no action is needed.
              */
            status_header(200, 'Ok');
            $this->customSuccessResponseHandle();
            $this->log_info($exception->getMessage(), $log_data);

            return false;
        } catch (Throwable $exception) { // NOTE: this will catch everything, including errors
            status_header(400, 'Unable to process transaction.'); // NOTE: I assume that 200 is default, so we only set 400 on error
            $this->log_error($exception->getMessage() . ', transaction: ' . $transactionPrefixedToken ?? '', $log_data);
            // Do not update transaction status, let scheduler handle it
            $this->updateTransactionWithResultAndStatusIfValid($exception->getMessage());

            return false;
        }
    }

    /**
     * Updates transaction with status and error message in result.
     * Status is updated only if $updateStatus param is set to true.
     * Transaction is updated only if transaction is not approved.
     * Use with care, we do not want to update successful transactions
     * Scheduler will take care of updating pending transactions
     * @param string $errorMessage
     * @param bool $updateStatus
     * @throws Exception Cannot modify a frozen row.
     */
    private function updateTransactionWithResultAndStatusIfValid(string $errorMessage, bool $updateStatus = false): void
    {
        if ($this->transaction instanceof Model_Whitelabel_Transaction) {
            $isTransactionApproved = $this->transaction->status == Helpers_General::STATUS_TRANSACTION_APPROVED;
            if (!$isTransactionApproved) {
                $transactionData = [
                    'additional_data' => serialize(
                        [
                            'result' => [
                                'error' => $errorMessage
                            ]
                        ]
                    ),
                ];

                if ($updateStatus) {
                    $transactionData['status'] = Helpers_General::STATUS_TRANSACTION_ERROR;
                }

                $this->transaction->set($transactionData);
                $this->transaction->save();
            }
        }
    }

    /**
     * Allows to retrieve the transaction amount field for comparison.
     * Can be overridden to validate the payment gateway data
     * if the returned value is expected to be different from the transaction value.
     */
    protected function getTransactionAmount(Model_Whitelabel_Transaction $transaction): string
    {
        return $transaction['amount_payment'];
    }

    /**
     * Should be implemented if payment gateway needs specific response on success.
     * "we understood your IPN, you do not need to retry sending it to us".
     * DO NOT STOP SCRIPT EXECUTION HERE, AS PAYMENT WILL FAIL.
     */
    protected function customSuccessResponseHandle(): void
    {
        return;
    }
}
