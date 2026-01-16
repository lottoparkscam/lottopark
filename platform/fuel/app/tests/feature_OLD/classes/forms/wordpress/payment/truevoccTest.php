<?php

/**
 * Description of Forms_Wordpress_Payment_TruevoCCTest
 */
class Forms_Wordpress_Payment_TruevoCCTest extends Test_Unit
{
    /**
     * @var Forms_Wordpress_Payment_TruevoCC
     */
    protected $object;
    
    /**
     *
     * @var array
     */
    private $whitelabel = null;

    /**
     *
     * @var Model_Whitelabel_Transaction
     */
    private $transaction = null;
    
    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->markTestIncomplete('Need rework and removal of dumps.');
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $this->transaction = Model_Whitelabel_Transaction::find_by_pk(1);
        
        $payment_method_id = Helpers_Payment_Method::TRUEVOCC;
        
        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);
        //var_dump($subtype[0]);
        $this->object = new Forms_Wordpress_Payment_TruevoCC(
            $this->whitelabel,
            [],
            $this->transaction,
            $subtype[0]
        );
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
    public function is_payment_credentials_set()
    {
        $payment_data = $this->object->get_payment_data();
        parent::assertFalse(empty($payment_data));
    }
    
    /**
     *
     */
    public function is_success_url_set()
    {
        $success_url = $this->object->get_success_url();
        parent::assertFalse(empty($success_url));
    }
    
    /**
     *
     */
    public function is_failure_url_set()
    {
        $failure_url = $this->object->get_failure_url();
        parent::assertFalse(empty($failure_url));
    }
    
    /**
     *
     */
    public function is_notifications_url_set()
    {
        $notifications_url = $this->object->get_notifications_url();
        parent::assertFalse(empty($notifications_url));
    }
    
    /**
     * @test
     */
    public function is_entity_id_set()
    {
        $this->object->get_payment_data();
        $entity_id = $this->object->get_entity_id();
        parent::assertFalse(empty($entity_id));
    }
    
    /**
     * @test
     */
    public function is_authorization_bearer_set()
    {
        $this->object->get_payment_data();
        
        $authorization_bearer = $this->object->get_authorization_bearer();
        
        parent::assertFalse(empty($authorization_bearer));
    }
    
    /**
     * @test
     */
    public function is_payment_type_set()
    {
        $payment_type = $this->object->get_payment_type();
        parent::assertFalse(empty($payment_type));
    }
    
    
    /**
     * @test
     */
    public function is_request_data_set()
    {
        $this->object->get_payment_data();
        
        $request_data = $this->object->get_request_data();
        
        parent::assertFalse(empty($request_data));
    }
    
    /**
     * @test
     */
    public function is_url_set()
    {
        $this->object->get_payment_data();
        
        $resource = "v1/checkouts";
        $url = $this->object->get_url($resource);
        
        parent::assertFalse(empty($url));
    }
    
    /**
     * @test
     */
    public function is_make_request_set()
    {
        $this->object->get_payment_data();
        
        $resource = "v1/checkouts";
        $url = $this->object->get_url($resource);
        
        $request_data = $this->object->get_request_data();
        
        $authrization_headers = $this->object->get_authorization_headers();
        
        $curl_request = $this->object->make_request($url, $request_data, 'POST', $authrization_headers);
        
        parent::assertFalse(is_null($curl_request));
    }
    
    /**
     *
     */
    public function is_transaction_set()
    {
        $token_full = 'LPP850914215';
        $token_int = $this->object->get_token($token_full);
        
        $result = $this->object->get_transaction($token_int);
        
        parent::assertFalse(is_null($result));
    }
}
