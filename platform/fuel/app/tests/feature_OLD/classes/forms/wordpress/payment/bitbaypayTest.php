<?php

/**
 * Description of Forms_Wordpress_Payment_BitbaypayTest
 */
class Forms_Wordpress_Payment_BitbaypayTest extends Test_Unit
{
    /**
     * @var Forms_Wordpress_Payment_Bitbaypay
     */
    protected $object;
    
    /**
     *
     * @var array
     */
    private $whitelabel = null;

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->markTestIncomplete('Need rework and removal of dumps.');
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $transaction = Model_Whitelabel_Transaction::find_by_pk(10);
        
        $payment_method_id = 15; // It is equals to BitBayPay
        
        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);
//        var_dump($subtype[0]);
        $this->object = new Forms_Wordpress_Payment_Bitbaypay();
        $this->object->set_whitelabel($this->whitelabel);
        $this->object->set_transaction($transaction);
        $this->object->set_model_whitelabel_payment_method($subtype[0]);
    }

    /**
     * @test
     */
    public function is_model_whitelabel_payment_method_set()
    {
        $model_whitelabel_payment_method = $this->object->get_model_whitelabel_payment_method();
        parent::assertFalse(empty($model_whitelabel_payment_method));
    }
    
    /**
     * @test
     */
    public function is_payment_credenctials_set()
    {
        $credentials_set = $this->object->get_payment_data();
        parent::assertFalse(empty($credentials_set));
    }
    
    /**
     * @test
     */
    public function is_success_url_set()
    {
        $success_url = $this->object->get_success_url();
        parent::assertFalse(empty($success_url));
    }
    
    /**
     * @test
     */
    public function is_failure_url_set()
    {
        $failure_url = $this->object->get_failure_url();
        parent::assertFalse(empty($failure_url));
    }
    
    /**
     * @test
     */
    public function is_notifications_url_set()
    {
        $notifications_url = $this->object->get_confirmation_url();
        parent::assertFalse(empty($notifications_url));
    }
    
    /**
     * @test
     */
    public function is_transaction_data_to_send_set()
    {
        $transaction_data_to_send = $this->object->get_transaction_data_to_send();
        parent::assertFalse(empty($transaction_data_to_send));
    }
    
    /**
     * @test
     */
    public function is_hash_hmac_set(): void
    {
        $hash_hmac_value = $this->object->get_hash_hmac();
        $this->assertFalse(is_null($hash_hmac_value));
    }
    
    /**
     * @test
     */
    public function is_UUID_set()
    {
        $uuid = $this->object->get_UUID_v4();
        parent::assertFalse(is_null($uuid));
    }
    
    /**
     * @test
     */
    public function is_request_headers_set()
    {
        $request_headers = $this->object->get_request_headers();
        //var_dump($request_headers);
        parent::assertFalse(empty($request_headers));
    }
    
    /**
     * @test
     */
    public function is_make_curl_request_set()
    {
        $curl_request = $this->object->get_make_curl_request();
        //var_dump($curl_request);
        parent::assertFalse(is_null($curl_request));
    }
    
    /**
     * @test
     */
    public function is_transaction_set()
    {
        $token_full = 'LPP850914215';
        $token_int = $this->object->get_token($token_full);
        
        $result = $this->object->get_transaction($token_int);
        
        parent::assertFalse(is_null($result));
    }
}
