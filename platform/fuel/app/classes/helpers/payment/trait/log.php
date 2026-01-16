<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 18.03.2019
 * Time: 15:02
 */

/**
 * Trait for logging payments.
 */
trait Helpers_Payment_Trait_Log
{

    /**
     *
     * @var array
     */
    protected $whitelabel = [];

    /**
     * Transaction
     * @var null|Model_Whitelabel_Transaction
     */
    protected $transaction = null;

    /**
     * This will be added at the beginning of the message.
     * @var string
     */
    private $name = 'Unnamed';

    /**
     * This is used to log under proper type. You can find const under Helpers_General::PAYMENT or Helpers_Payment_Method::
     * @var int
     */
    private $method = Helpers_Payment_Method::TEST;

    private array $logData = [];

    /**
     * Log payment in database.
     * @param string message of the log, will prefixed by name of the payment.
     * @param int $type type of the log - info on default, types can be found in Helpers_General::TYPE_
     * @param array $data
     * @return void
     */
    protected function log(
        string $message,
        int $type = Helpers_General::TYPE_INFO,
        array $data = []
    ): void {
        if (empty($data)) {
            $data = null;
        }
        
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        
        Model_Payment_Log::add_log(
            $type,
            Helpers_General::PAYMENT_TYPE_OTHER,
            $this->method,
            null,
            $this->whitelabel['id'] ?? null,
            $this->transaction->id ?? null,
            "{$this->name} - $message",
            $data,
            $whitelabel_payment_method_id
        );
    }

    /**
     * Log success of the payment.
     * @param $message
     * @param array $data
     * @return void
     */
    protected function log_success(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_SUCCESS, $data);
    }

    /**
     * Log failure of the payment.
     * @param $message
     * @param array $data
     * @return void
     */
    protected function log_error(string $message, array $data = []): void
    {
        $logData = !empty($this->logData) ? $this->logData : $data;
        $this->log($message, Helpers_General::TYPE_ERROR, $logData);
    }

    /**
     * Log info for the payment.
     * @param $message
     * @param array $data
     * @return void
     */
    protected function log_info(string $message, array $data = []): void
    {
        $this->log($message, Helpers_General::TYPE_INFO, $data);
    }

    /**
     * Additional data to add to the payment log on error.
     * For example the method allows to add request body:
     *
     * $this->setLogData($checkoutRequest->toArray());
     */
    protected function setLogData(array $data): void
    {
        $this->logData = $data;
    }
}
