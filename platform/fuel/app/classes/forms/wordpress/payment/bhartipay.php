<?php

require_once APPPATH . 'vendor/bhartipay/bppg_helper.php';

use Fuel\Core\Config;
use Fuel\Core\Response;
use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Bhartipay extends Forms_Wordpress_Payment_Base implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    use Traits_Payment_Method_Currency;

    private FileLoggerService $fileLoggerService;

    const REQUEST_URL_TEST = 'https://uat.bhartipay.com/crm/jsp/paymentrequest';
    const REQUEST_URL_PRODUCTION = 'https://merchant.bhartipay.com/crm/jsp/paymentrequest';

    const RESPONSE_CODE_SUCCESS = '000';
    const RESPONSE_CODE_PENDING = '006';

    /**
     *
     * @var int
     */
    protected $payment_method = Helpers_Payment_Method::BHARTIPAY;

    /**
     *
     * @param array                                $whitelabel
     * @param array                                $user
     * @param Model_Whitelabel_Transaction|null    $transaction
     * @param Model_Whitelabel_Payment_Method|null $model_whitelabel_payment_method
     * @param Validation|null                      $user_validation
     */
    public function __construct(
        ?array $whitelabel = [],
        ?array $user = [],
        ?Model_Whitelabel_Transaction $transaction = null,
        ?Model_Whitelabel_Payment_Method $model_whitelabel_payment_method = null,
        ?Validation $user_validation = null
    )
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->model_whitelabel_payment_method = $model_whitelabel_payment_method;
    }

    /**
     *
     * @return string
     */
    protected function get_result_url(): string
    {
        $whitelabel_payment_method_id = $this->get_whitelabel_payment_method_id();
        if (empty($whitelabel_payment_method_id)) {
            $this->log_error("Lack of whitelabel_payment_method_id!");
            exit(_("Bad request! Please contact us!"));
        }

        return lotto_platform_home_url_without_language() .
            '/order/result/' .
            Helpers_Payment_Method::BHARTIPAY_URI . '/' .
            $whitelabel_payment_method_id . '/';
    }

    /**
     *
     * @return void
     */
    protected function check_transaction(): void
    {
        if (empty($this->transaction)) {
            status_header(400);
            $this->log_error('Incorrect transaction');
            exit(_("Bad request! Please contact us!"));
        }
    }

    /**
     *
     * @return void
     */
    protected function check_merchant_settings(): void
    {
        $this->check_credentials();
        $this->check_transaction();
    }

    /**
     *
     * @param array $response_data
     *
     * @return bool
     */
    protected function check_response_has_required_fields(array $response_data): bool
    {
        $required_fields = ['RESPONSE_CODE', 'ORDER_ID', 'AMOUNT', 'CURRENCY_CODE', 'TXN_ID', 'HASH'];
        $missing_fields = [];

        foreach ($required_fields as $required_field) {
            if (!array_key_exists($required_field, $response_data)) {
                $missing_fields[] = $required_field;
            }
        }

        if (!empty($missing_fields)) {
            $this->log_error('Missing field in response', $missing_fields);
            // Tom: temporary e-mail about BhartiPay problem
            if (count($missing_fields) == 1 && $missing_fields[0] == "HASH") {
                $body = "ORDER_ID: " . ($response_data["ORDER_ID"] ?? "") . "\r\n";
                $body .= "TXN_ID: " . ($response_data["TXN_ID"] ?? "") . "\r\n";
                $body .= "PAY_ID: " . ($response_data["PAY_ID"] ?? "") . "\r\n";
                $body .= "PAYMENT_ID: " . ($response_data["PAYMENT_ID"] ?? "") . "\r\n";

                Config::load("lotteries", true);
                $recipients = Config::get("lotteries.support_errors_emails");

                \Package::load('email');
                $email = \Email::forge();
                $email->from('noreply@' . \Helpers_General::get_domain(), 'Lotto Emergency');
                $email->to($recipients);
                $title = "Lotto Emergency: BhartiPay - New transaction with empty HASH";
                $email->subject($title);
                $email->body($body);
                try {
                    $email->send();
                } catch (Exception $e) {
                    $error_message = "There is a problem with delivering the mail. " .
                        "Description of error: " . $e->getMessage();
                    $this->fileLoggerService->error(
                        $error_message
                    );
                }
            }

            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    protected function get_request_url($is_test): string
    {
        if ($is_test) {
            return self::REQUEST_URL_TEST;
        }

        return self::REQUEST_URL_PRODUCTION;
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $bhartipay_transaction = new BPPGModule();

        $payment_params = $this->get_payment_data();
        if (empty($payment_params['bhartipay_pay_id']) ||
            empty($payment_params['bhartipay_secret_key'])
        ) {
            $this->transaction->set([
                'status' => Helpers_General::STATUS_TRANSACTION_ERROR,
                'payment_method_type' => Helpers_General::PAYMENT_TYPE_OTHER,
                'whitelabel_payment_method_id' => $this->model_whitelabel_payment_method['id']
            ]);
            $this->transaction->save();

            $this->log_error(
                "Empty Pay Id, Secret Key or Test."
            );

            exit(_("Bad request! Please contact us!"));
        }

        $this->transaction->whitelabel_payment_method_id = intval($this->model_whitelabel_payment_method->id);
        $this->transaction->payment_method_type = Helpers_General::PAYMENT_TYPE_OTHER;
        $this->transaction->save();

        $payment_currency = $this->get_payment_currency($this->transaction->payment_currency_id);
        $payment_currency_iso_code = $this->get_currency_iso_code(
            $this->model_whitelabel_payment_method->payment_method_id,
            $payment_currency
        );

        $order_id = $this->get_prefixed_transaction_token();
        $return_url = $this->get_result_url();
        $request_url = $this->get_request_url($payment_params['bhartipay_test']);

        $bhartipay_transaction->setPayId($payment_params['bhartipay_pay_id']);
        $bhartipay_transaction->setSalt($payment_params['bhartipay_secret_key']);
        $bhartipay_transaction->setPgRequestUrl($request_url);
        $bhartipay_transaction->setReturnUrl($return_url);
        $bhartipay_transaction->setAmount(intval($this->transaction->amount_payment * 100));
        $bhartipay_transaction->setCurrencyCode($payment_currency_iso_code);
        $bhartipay_transaction->setOrderId($order_id);
        $bhartipay_transaction->setTxnType('SALE');
        $bhartipay_transaction->setCustEmail($this->user['email']);
        $bhartipay_transaction->setCustName($this->user['name'] . ' ' . $this->user['surname']);
        $bhartipay_transaction->setCustFirstName($this->user['name']);
        $bhartipay_transaction->setCustLastName($this->user['surname']);
        $bhartipay_transaction->setCustStreetAddress1($this->user['address_1']);
        $bhartipay_transaction->setCustCity($this->user['city']);
        $bhartipay_transaction->setCustState($this->user['state']);
        $bhartipay_transaction->setCustCountry($this->user['country']);
        $bhartipay_transaction->setCustZip($this->user['zip']);
        $bhartipay_transaction->setCustPhone(empty($this->user['phone']) ? 'Nan' : $this->user['phone']);

        $post_data = $bhartipay_transaction->createTransactionRequest();

        $this->log_info('Redirect customer', $post_data);
        $bhartipay_transaction->redirectForm($post_data);
    }

    /**
     *
     * @return void
     */
    public function process_checking(): void
    {
        $response_data = Input::param();
        $this->log_info('Received response', $response_data);
        $is_valid = $this->check_response_has_required_fields($response_data);

        $this->transaction = $this->get_transaction_by_token($response_data['ORDER_ID'] ?? null);

        if (empty($this->user)) {
            $this->log_error('Payment invalid - user not found', $response_data);
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));

            return;
        }

        $this->check_merchant_settings();
        $data = $this->get_payment_data();

        //Validation
        $bhartipay_helper = new BPPGModule();
        $bhartipay_helper->setSalt($data['bhartipay_secret_key']);
        $is_valid &= $bhartipay_helper->validateResponse($response_data);
        $is_valid &= intval($this->transaction->amount_payment * 100) == ($response_data['AMOUNT'] ?? null);

        $payment_currency = $this->get_payment_currency($this->transaction->payment_currency_id);
        $payment_currency_iso_code = $this->get_currency_iso_code(
            $this->model_whitelabel_payment_method->payment_method_id,
            $payment_currency
        );
        $is_valid &= $payment_currency_iso_code == ($response_data['CURRENCY_CODE'] ?? null);

        if (!$is_valid) {
            $this->log_error('Payment invalid', $response_data);
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));

            return;
        }

        switch ($response_data['RESPONSE_CODE']) {
            case self::RESPONSE_CODE_SUCCESS:
                Lotto_Helper::accept_transaction($this->transaction, $response_data['TXN_ID'], $response_data, $this->whitelabel);
                $this->log_success('Payment succeess', $response_data);
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
                break;
            case self::RESPONSE_CODE_PENDING:
                $this->log_info('Payment processing', $response_data);
                break;
            default:
                $this->log_error('Payment invalid', $response_data);
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));

                return;
        }
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $this->process_form();
    }

    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string                       $out_id
     * @param array                        $data
     *
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool
    {
        return false;
    }
}
