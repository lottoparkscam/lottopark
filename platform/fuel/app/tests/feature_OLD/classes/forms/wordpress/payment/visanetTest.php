<?php

/**
 * Description of Forms_Wordpress_Payment_VisaNetTest
 */
class Forms_Wordpress_Payment_VisaNetTest extends Test_Unit
{

    /**
     * @var Forms_Wordpress_Payment_VisaNet
     */
    protected $object;

    /**
     * In that case I want to have possibility to test add/subtract features
     * in transaction without any further result on DB.
     *
     * @var bool
     */
    protected $in_transaction = true;

    /**
     *
     * @var int
     */
    protected $start_auto_increment_ticket = 1;

    /**
     *
     * @var int
     */
    protected $start_auto_increment_ticket_line = 1;

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
        parent::setUp();

        if ($this->in_transaction) {
            DB::start_transaction();

            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment 
                FROM whitelabel_user_ticket";
            $result_tickets = DB::query($query)->execute();
            $this->start_auto_increment_ticket = $result_tickets[0]['current_auto_increment'];

            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment_t_line 
                FROM whitelabel_user_ticket_line";
            $result_tickets_lines = DB::query($query)->execute();
            $this->start_auto_increment_ticket_line = $result_tickets_lines[0]['current_auto_increment_t_line'];
        }

        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);

        $this->transaction = Model_Whitelabel_Transaction::find_by_pk(75);
        
        $payment_method_id = Helpers_Payment_Method::VISANET;
        
        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);
        
        $user_id = 1;
        $user = Model_Whitelabel_User::get_user_with_currencies_by_id_and_whitelabel(
            $user_id,
            $this->whitelabel
        );

        // THIS IS EXAMPLE
        $this->object = new Forms_Wordpress_Payment_VisaNet(
            $this->whitelabel,
            $user,
            $this->transaction,
            $subtype[0]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->in_transaction) {
            DB::rollback_transaction();

            $query = "ALTER TABLE whitelabel_user_ticket AUTO_INCREMENT = " . $this->start_auto_increment_ticket;
            DB::query($query)->execute();

            $query = "ALTER TABLE whitelabel_user_ticket_line AUTO_INCREMENT = " . $this->start_auto_increment_ticket_line;
            DB::query($query)->execute();
        }
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
        $payment_credentials = $this->object->get_payment_data();
//        var_dump($payment_credentials);
        parent::assertFalse(empty($payment_credentials));
    }

    /**
     * @test
     */
    public function is_url_set()
    {
        $this->object->get_payment_data();
        
        $resource = "/api.security/v1/security";
        $url = $this->object->get_url($resource);
        
//        var_dump($url);
        
        parent::assertFalse(empty($url));
    }

    /**
     * @test
     */
    public function is_authorization_headers_set()
    {
        $this->object->get_payment_data();
        $test_data = $this->object->get_authorization_headers();
        
//        var_dump($test_data);
        
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_authorization_credentials_set()
    {
        $test_data = $this->object->get_authorization_credentials();
//        var_dump($test_data);
        
        parent::assertFalse(empty($test_data));
    }
    
    /**
     *
     */
    public function is_security_token_set()
    {
        $test_data = $this->object->get_security_token();
        parent::assertFalse(empty($test_data));
    }
    
    /**
     *
     */
    public function is_make_request_step1_set()
    {
        $this->object->get_payment_data();
        
        $resource = "/api.security/v1/security";
        $url = $this->object->get_url($resource);
        var_dump($url);
        
        $authorization_headers = $this->object->get_authorization_headers();
        var_dump($authorization_headers);
        
        $test_data = $this->object->make_request($url, null, 'POST', $authorization_headers);
        
        var_dump($test_data);
        
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_merchant_id_set()
    {
        $test_data = $this->object->get_merchant_id();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_client_ip_set()
    {
        $test_data = $this->object->get_client_ip();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_payment_amount_set()
    {
        $test_data = $this->object->get_payment_amount();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_channel_set()
    {
        $test_data = $this->object->get_channel();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_integration_text_set()
    {
        $test_data = $this->object->get_integration_text();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_communication_session_data_set()
    {
        $test_data = $this->object->get_communication_session_data();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_authorization_headers_for_communication_session_set()
    {
        $secure_token = "1234567890";
        $test_data = $this->object->get_authorization_headers_for_communication_session($secure_token);
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     *
     */
    public function is_make_request_step2_set()
    {
        $this->object->get_payment_data();
        
        $resource = "/api.security/v1/security";
        $url = $this->object->get_url($resource);
        var_dump($url);
        
        $authorization_headers = $this->object->get_authorization_headers();
        var_dump($authorization_headers);
        
        $secure_token = $this->object->make_request(
            $url,
            null,
            'POST',
            $authorization_headers
        );
        
        var_dump($secure_token);
        
        $merchan_id = $this->object->get_merchant_id();
        
        $resource_for_communication_session = "/api.ecommerce/v2/ecommerce/token/session/" . $merchan_id;
        $url_for_communication_session = $this->object->get_url($resource_for_communication_session);
        
        var_dump($url_for_communication_session);
        
        $authorization_headers_for_communication_session = $this->object->get_authorization_headers_for_communication_session($secure_token);
        var_dump($authorization_headers_for_communication_session);
        
        $post_data = $this->object->get_communication_session_data();
        
        $response_for_communication_session = $this->object->make_request(
            $url_for_communication_session,
            $post_data,
            'POST',
            $authorization_headers_for_communication_session
        );
        
        var_dump($response_for_communication_session);
        
        parent::assertFalse(empty($response_for_communication_session));
    }
    
    /**
     * @test
     */
    public function is_return_url_set()
    {
        $this->object->get_payment_data();
        
        $test_data = $this->object->get_result_url();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_source_url_set()
    {
        $this->object->get_payment_data();
        
        $test_data = $this->object->get_source_url();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_session_token_set()
    {
        $response = [
            'sessionKey' => '1231231230'
        ];
        $test_data = $this->object->get_session_token($response);
        
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_merchant_name_set()
    {
        $test_data = $this->object->get_merchant_name();
        
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_purchase_number_set()
    {
        $test_data = $this->object->get_purchase_number();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_expiration_minutes_set()
    {
        $response = [
            'expirationTime' => '1569867198547'
        ];
        $test_data = $this->object->get_expiration_minutes($response);
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
    
    /**
     * @test
     */
    public function is_timeout_url_set()
    {
        $test_data = $this->object->get_timeout_url();
        var_dump($test_data);
        parent::assertFalse(empty($test_data));
    }
}
