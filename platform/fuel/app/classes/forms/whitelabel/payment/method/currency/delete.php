<?php

/**
 * Description of Forms_Whitelabel_Payment_Currency_Delete
 */
class Forms_Whitelabel_Payment_Method_Currency_Delete extends Forms_Main
{
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(
        int $source,
        array $whitelabel
    ) {
        $this->source = $source;
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @param int $whitelabel_payment_method_id
     * @param int $delete_id
     * @return int
     */
    public function process_form(
        int $whitelabel_payment_method_id = null,
        int $delete_id = null
    ): int {
        if (empty($whitelabel_payment_method_id) ||
            $whitelabel_payment_method_id <= 0
        ) {
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);
        
        if ($whitelabel_payment_method === null ||
            (int)$whitelabel_payment_method->whitelabel_id !== (int)$this->whitelabel['id']
        ) {
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        if (isset($delete_id) && $delete_id > 0) {
            $whitelabel_payment_method_currency = Model_Whitelabel_Payment_Method_Currency::find_by_pk($delete_id);
            
            if ($whitelabel_payment_method_currency !== null) {
                $set = [
                    "is_enabled" => 0
                ];
                $whitelabel_payment_method_currency->set($set);
                $whitelabel_payment_method_currency->save();
            } else {
                return self::RESULT_WRONG_ID_GIVEN;
            }
        } else {
            return self::RESULT_WRONG_ID_GIVEN;
        }
        
        return self::RESULT_OK;
    }
}
