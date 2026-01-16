<?php

/**
 *
 */
class Model_Payment_Method_Currency extends Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'payment_method_currency';
    
    /**
     *
     * @var array
     */
    protected static $cache_list = [
        "model_payment_method.paymentmethodcurrencies"
    ];
}
