<?php

/**
 * Description of Forms_Wordpress_Bonuses_WelcomeTest
 */
class Forms_Wordpress_Bonuses_WelcomeTest extends Test_Unit
{
    /**
     * @var Forms_Wordpress_Bonuses_Welcome
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
        
        $user_id = 1;
        $user = Model_Whitelabel_User::get_user_with_currencies_by_id_and_whitelabel(
            $user_id,
            $this->whitelabel
        );
        
        $this->object = new Forms_Wordpress_Bonuses_Welcome(
            $this->whitelabel,
            $user
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
    public function is_bonus_for_whitelabel_set(): void
    {
        $bonus_for_whitelabel = $this->object->get_bonus_for_whitelabel();
        $this->markTestIncomplete('Error, test case need work');
        parent::assertFalse(empty($bonus_for_whitelabel));
    }
    
    /**
     * @test
     */
    public function is_lottery_set()
    {
        $bonus_for_whitelabel = $this->object->get_bonus_for_whitelabel();
        $lottery = $this->object->get_lottery($bonus_for_whitelabel);

        $this->markTestIncomplete('Error, test case need work');
        parent::assertFalse(empty($lottery));
    }
    
    /**
     * @test
     */
    public function is_user_set()
    {
        $user = $this->object->get_user();
        parent::assertFalse(empty($user));
    }
    
    /**
     * @test
     */
    public function is_process_working_fine()
    {
        $results_expected = [
            Forms_Wordpress_Bonuses_Welcome::RESULT_OK,
            Forms_Wordpress_Bonuses_Welcome::RESULT_WITH_ERRORS,
            Forms_Wordpress_Bonuses_Welcome::RESULT_NO_BONUS
        ];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $result = $this->object->process_form();
        
        parent::assertTrue(in_array($result, $results_expected));
    }
    
    /**
     * @test
     */
    public function is_bonus_ticket_set()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $result = $this->object->process_form();
        $bonus_ticket = $this->object->get_new_bonus_ticket();

        $this->markTestIncomplete('Error, test case need work');
        parent::assertInstanceOf(Model_Whitelabel_User_Ticket::class, $bonus_ticket);
    }
    
    /**
     * @test
     */
    public function is_full_token_set()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $result = $this->object->process_form();
        $full_ticket_token = $this->object->get_ticket_full_token();

        $this->markTestIncomplete('Error, test case need work');
        parent::assertFalse(empty($full_ticket_token));
    }
    
    /**
     * @test
     */
    public function is_email_data_set()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $result = $this->object->process_form();
        $email_data = $this->object->get_email_data();

        $this->markTestIncomplete('Error, test case need work');
        parent::assertFalse(empty($email_data));
    }
}
