<?php

/**
 *
 */
trait Traits_Payment_Method_Currency
{
    /**
     * Check currency for payment_method if it is supporeted
     * and return iso code for that currency
     * If currency not found fallback iso_code value equal 978 (EUR currency)
     * will be returned
     *
     * @param int $payment_method_id
     * @param string $currency_code_to_check
     * @return string
     */
    private function get_currency_iso_code(
        int $payment_method_id,
        string $currency_code_to_check
    ): string {
        $currency_iso_code = "";
        
        list(
            $supported_currencies,
            $return_full
        ) = $this->get_supported_currencies($payment_method_id, true);
        
        if (in_array($currency_code_to_check, array_keys($supported_currencies))) {
            $currency_iso_code = (string)$supported_currencies[$currency_code_to_check];
        } else {
            $currency_iso_code = "978"; // EUR - as fallback in the case of lack of currency
        }
        
        return $currency_iso_code;
    }
    
    /**
     *
     * @param int $payment_method_id
     * @param bool $iso_code_as_values If true results will be return
     * as code as key and iso_code as value within $currency_codes variable
     * @return array
     */
    public function get_supported_currencies(
        int $payment_method_id,
        bool $iso_code_as_values = false
    ): array {
        $return_full = false;
        $currency_codes = [];
        
        // If it will be empty array it will return all list of available currencies
        $model_supported_currency_codes = Model_Payment_Method_Supported_Currency::get_list_by_payment_method_id($payment_method_id);

        if (!empty($model_supported_currency_codes)) {
            $supported_currency_codes = [];
            if (!$iso_code_as_values) {
                foreach ($model_supported_currency_codes as $currency) {
                    $supported_currency_codes[] = (string)$currency["code"];
                }
                asort($supported_currency_codes);

                $currency_codes = array_values($supported_currency_codes);
            } else {
                foreach ($model_supported_currency_codes as $currency) {
                    $supported_currency_codes[(string)$currency["code"]] = (string)$currency["iso_code"];
                }
                ksort($supported_currency_codes);
                
                $currency_codes = $supported_currency_codes;
            }
        } else {
            $return_full = true;
        }
        
        return [
            $currency_codes,
            $return_full
        ];
    }
    
    /**
     * Function process all supported currency codes and checked them
     * with those from our DB and returns as table where
     * key is currency ID and value is currency code!
     *
     * @param int $payment_method_id If it is null full list will be processed
     * @return array
     */
    public function get_supported_currencies_with_currency_id_as_key(
        int $payment_method_id = null
    ): array {
        $supported_currencies = [];
        $currency_codes = [];
        $return_full = false;
        
        if (is_null($payment_method_id)) {
            $return_full = true;
        } else {
            list(
                $currency_codes,
                $return_full
            ) = $this->get_supported_currencies($payment_method_id);
        }
        
        $currencies = Helpers_Currency::getCurrencies();
        
        if ($return_full) {
            foreach ($currencies as $currency) {
                $supported_currencies[$currency['id']] = $currency['code'];
            }
        } else {
            foreach ($currencies as $key => $currency) {
                if (in_array($currency['code'], $currency_codes)) {
                    $supported_currencies[$currency['id']] = $currency['code'];
                }
            }
        }
        
        asort($supported_currencies);
        
        return $supported_currencies;
    }
    
    /**
     * This function is to check if given currency is supported by
     * payment method (which is child of that class and within that
     * could be defined list of supported currencies in the function
     * get_supported_currencies_with_currency_id_as_key())
     *
     * @param int $payment_method_id If it is null, full list will be processed
     * @param int $currency_id
     * @return bool
     */
    public function is_currency_supported(
        int $payment_method_id = null,
        int $currency_id = null
    ): bool {
        $result = false;
        $supporeted_currencies = $this->get_supported_currencies_with_currency_id_as_key($payment_method_id);
        
        $currencies_ids = array_keys($supporeted_currencies);
        
        if (in_array($currency_id, $currencies_ids)) {
            $result = true;
        }
        
        return $result;
    }
}
