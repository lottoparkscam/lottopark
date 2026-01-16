<?php

use Fuel\Core\Input;
use Repositories\Orm\TransactionRepository;
use Repositories\WhitelabelPaymentMethodRepository;
use Repositories\WhitelabelRepository;

/**
 * Description of Forms_Order_Confirm
 */
class Forms_Order_Confirm extends Forms_Main
{

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var string
     */
    private $ip = "";
    
    /**
     *
     * @var string
     */
    private $section = "";
    
    /**
     *
     * @var int
     */
    private $whitelabel_payment_method_id = 0;

    private TransactionRepository $transactionRepository;

	/**
	 * @param array $whitelabel
	 * @param string $ip
	 * @param string $section
	 * @param int $whitelabel_payment_method_id
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
    public function __construct(
        array $whitelabel,
        string $ip,
        string $section = "",
        int $whitelabel_payment_method_id = 0
    ) {
        $this->whitelabel = $whitelabel;
        $this->ip = $ip;
        $this->section = $section;
        $this->whitelabel_payment_method_id = $whitelabel_payment_method_id;
        $this->transactionRepository = Container::get(TransactionRepository::class);
    }

    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

	/**
	 * NOTICE! AT THIS MOMENT THE EMERCHANTPAY PAYMENT IS NOT CONSIDERED
	 *
	 * @return void
	 * @throws Exception
	 */
    public function process_form(): void
    {
        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_URI_as_key();
                
        if (empty($list_of_payment_methods_classes[$this->section])) {
            status_header(400);
            
            Model_Payment_Log::add_log(
                Helpers_General::TYPE_ERROR,
                Helpers_General::PAYMENT_TYPE_OTHER,
                null,
                null,
                $this->whitelabel['id'],
                null,
                "Unknown payment method.",
                [$this->section]
            );
            
            exit(_("Bad request! Please contact us!"));
        }
        
        $transaction = null;
        $data = [];
        $out_id = null;
        
        // Get the name of the payment class
        $name_of_class_form = $list_of_payment_methods_classes[$this->section];
        
        // Creation of the payment class
        $other_type_payment_method_form = new $name_of_class_form($this->whitelabel);
        $other_type_payment_method_form->set_ip($this->ip);
        $other_type_payment_method_form->set_whitelabel_payment_method_id($this->whitelabel_payment_method_id);
        
        $ok = $other_type_payment_method_form->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        $transactionShouldBeProcessed = $transaction !== null && $ok && $transaction['status'] !== Helpers_General::STATUS_TRANSACTION_APPROVED;
        if ($transactionShouldBeProcessed) {
            // we want to override the whitelabel object here based on the whitelabel_id from the transaction in order
            // to send correct emails for ZEN payments that are processed by lottopark from other whitelabels
            $isNotLottopark = (int)$transaction['whitelabel_id'] !== 1;
            if ($isNotLottopark) {
                $whitelabelPaymentMethodRepository = Container::get(WhitelabelPaymentMethodRepository::class);
                $zenPaymentMethodIds = $whitelabelPaymentMethodRepository->getAllWhitelabelPaymentMethodIdsByMethodId(Helpers_Payment_Method::ZEN_ID);
                if (in_array($other_type_payment_method_form->get_whitelabel_payment_method_id(), $zenPaymentMethodIds)) {
                    $whitelabelRepository = Container::get(WhitelabelRepository::class);
                    $whitelabel = $whitelabelRepository->findOneById($transaction['whitelabel_id']);
                    $this->whitelabel = $whitelabel->to_array();
                }
            }

            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $this->whitelabel
            );
            
            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }
        // prevent wordpress output
        
        exit();
    }
}
