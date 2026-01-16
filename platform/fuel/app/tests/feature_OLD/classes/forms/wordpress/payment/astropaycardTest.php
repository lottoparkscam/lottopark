<?php

/**
 * Description of Forms_Wordpress_Payment_AstropaycardTest
 */
class Forms_Wordpress_Payment_AstropaycardTest extends Test_Unit
{
    /**
     * @var Forms_Wordpress_Payment_Astropaycard
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
        
        //$transaction = Model_Whitelabel_Transaction::find_by_pk(10);
        
        $payment_method_id = Helpers_Payment_Method::ASTRO_PAY_CARD; // It is equals to AstroPayCard
        
        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);

        $this->object = new Forms_Wordpress_Payment_Astropaycard();
        $this->object->set_whitelabel($this->whitelabel);
        //$this->object->set_transaction($transaction);
        $this->object->set_model_whitelabel_payment_method($subtype[0]);
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
        var_dump($payment_credentials);
        parent::assertFalse(empty($payment_credentials));
    }
    
    /**
     * @test
     */
    public function is_get_api_url_set()
    {
        $api_url = $this->object->get_api_url();
        var_dump($api_url);
        parent::assertFalse(is_null($api_url));
    }
    
    /**
     * @test
     */
    public function is_get_transtatus_url_set()
    {
        $transtatus_url = $this->object->get_transtatus_url();
        var_dump($transtatus_url);
        parent::assertFalse(is_null($transtatus_url));
    }
    
    /**
     * @test
     */
    public function is_get_validator_url_set()
    {
        $validator_url = $this->object->get_validator_url();
        var_dump($validator_url);
        parent::assertFalse(is_null($validator_url));
    }
    
    /**
     * @test
     */
    public function is_get_general_communication_settings_set()
    {
        $general_communication_settings = $this->object->get_general_communication_settings();
        
        var_dump($general_communication_settings);
        
        parent::assertFalse(is_null($general_communication_settings));
    }
    
    /**
     * @test
     */
    public function is_auth_transaction_set()
    {
        $x_card_num = "1616548016998793";
        $x_card_code = "2249";
        $x_exp_date = "06/2020";
        $x_amount = 1.01;
        $x_currency = "EUR";
        $x_unique_id = "LPU199976457";
        $x_invoice_num = "LPD123456789";
        
        $auth_transaction = $this->object->auth_transaction(
            $x_card_num,
            $x_card_code,
            $x_exp_date,
            $x_amount,
            $x_currency,
            $x_unique_id,
            $x_invoice_num
        );
        
        var_dump($auth_transaction);
        
        parent::assertFalse(empty($auth_transaction));
    }
}
