<?php

use Fuel\Core\Input;

/**
 * Receives payment from (after it was finalized) from easy payment gateway.
 */
final class Helpers_Payment_Easypaymentgateway_Receiver extends Helpers_Payment_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    /**
     * List of ip's allowed to send notification.
     */
    const ALLOWED_IPS_STAGING = [
        '104.199.46.0',
        '35.189.207.233',
    ];

    /**
     * List of ip's allowed to send notification.
     */
    const ALLOWED_IPS_PRODUCTION = [
        '104.155.113.8',
        '35.195.204.5',
        '35.234.68.27',
        '35.189.209.142',
        '35.242.194.2',
    ];

    /**
     * Method name.
     */
    const METHOD_NAME = Helpers_Payment_Method::EASY_PAYMENT_GATEWAY_NAME;

    /**
     * Method id.
     */
    const METHOD_ID = Helpers_Payment_Method::EASY_PAYMENT_GATEWAY;

    /**
     * Fetch input fields.
     *
     * @return array Input fields - result of Fuel\Input::method.
     */
    protected function fetch_input_fields(): array
    {
        return Input::xml();
    }

    /**
     * Get inner id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_transaction_id_from_input_fields(array $input_fields): string
    {
        return $this->operation['merchantTransactionId'];
    }
    /**
     * Get amount in the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_amount(array $input_fields): string
    {
        return $this->operation['amount'];
    }
    /**
     * Get outer (in the payment solution) id of the transaction.
     *
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return string
     */
    protected function get_transaction_outer_id(array $input_fields): string
    {
        return $this->operation['payFrexTransactionId'];
    }
    /**
     * Validate result of the transaction, override if it's not standard integer result code.
     *
     * @param array $input_fields $input_fields input fields received from notification @see fetch_input_fields()
     * @return void
     */
    protected function validate_result(array $input_fields): void
    {
        // check request status
        if (($input_fields['status'] ?? null) !== 'SUCCESS') {
            throw new ErrorException('Request status different than success');
        }

        // check status of the operation (transaction)
        if (($this->operation['status'] ?? null) !== 'SUCCESS') {
            throw new ErrorException('Operation returned failed status, transaction: ' . $this->get_transaction_id_from_input_fields($input_fields));
        }
    }

    /**
     * Operation from epg input fields.
     *
     * @var array
     */
    private $operation;

    /**
     * Concrete validation of received input fields done by child.
     * @param array $input_fields input fields received from notification @see fetch_input_fields()
     * @return void
     * @throws Throwable any error in execution will be automatically caught and logged.
     */
    protected function validate_input_fields(array $input_fields): void
    {
        // IMPORTANT: Assumption we handle only one operation per transaction (epg has place for more than one here)
        $this->operation = $input_fields['operations']['operation'] ?? null;
        if (empty($this->operation)) {
            throw new \Exception('Unable to find operation');
        }
        // check if operation has int indexes
        if (isset($this->operation[0])) {
            $this->operation = $this->operation[count($this->operation) - 1]; // extract last item from operation and set it as operation, mind explode structure.
        }
    }
    
    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
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
        $fileLoggerService = Container::get(FileLoggerService::class);

        $message = "In confirm payment. ";

        $fileLoggerService->error(
            $message
        );
        
        $ok = $this->receive_transaction($transaction, $out_id, $data);
        
        return $ok;
    }
}
