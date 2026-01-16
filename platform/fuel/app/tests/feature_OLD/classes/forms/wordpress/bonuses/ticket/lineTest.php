<?php

/**
 * Description of line
 */
class Forms_Wordpress_Bonuses_Ticket_LineTest extends Test_Feature
{
    /**
     * @var Forms_Wordpress_Bonuses_Ticket_Line
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
    protected $start_auto_increment = 1;
    
    /**
     *
     * @var array
     */
    private $whitelabel = null;

    private $ncount = 5;
    private $nrange = 69;
    private $bcount = 1;
    private $brange = 26;
    private $bextra = 0;
    
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
                FROM whitelabel_user_ticket_line";
            $result = DB::query($query)->execute();
            $this->start_auto_increment = $result[0]['current_auto_increment'];
        }
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $lottery_id = 11;
        $lottery = Model_Lottery::get_single_row_by_id($lottery_id);
        
        $lottery_type = [
            'ncount' => $this->ncount,
            'nrange' => $this->nrange,
            'bcount' => $this->bcount,
            'brange' => $this->brange,
            'bextra' => $this->bextra,
        ];
        
        $whitelabel_user_ticket_id = 1;
        $this->object = new Forms_Wordpress_Bonuses_Ticket_Line(
            $lottery,
            $lottery_type,
            $whitelabel_user_ticket_id
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
            
            $query = "ALTER TABLE whitelabel_user_ticket_line AUTO_INCREMENT = " . $this->start_auto_increment;
            DB::query($query)->execute();
        }
    }
    
    /**
     * @test
     */
    public function is_numbers_set()
    {
        $numbers = $this->object->generate_numbers();
        parent::assertFalse(empty($numbers));
    }
    
    /**
     * @test
     */
    public function is_bonus_numbers_set()
    {
        $bonus_numbers = $this->object->generate_bonus_numbers();
        
        if ($this->bextra === 0 && $this->bcount > 0) {
            parent::assertFalse(empty($bonus_numbers));
        } else {
            parent::assertTrue(empty($bonus_numbers));
        }
    }
    
    /**
     * @test
     */
    public function is_get_whitelabel_user_ticket_id_set()
    {
        $whitelabel_user_ticket_id = $this->object->get_whitelabel_user_ticket_id();
        parent::assertFalse(empty($whitelabel_user_ticket_id));
    }
    
    /**
     * @test
     */
    public function is_line_set_properly()
    {
        $this->object->generate_numbers();
        $this->object->generate_bonus_numbers();
        
        $check_line = $this->object->check_line();
        
        parent::assertEquals(Forms_Wordpress_Bonuses_Ticket_Line::RESULT_OK, $check_line);
    }
    
    /**
     * @test
     */
    public function is_full_set_set(): void
    {
        $set = $this->object->get_prepared_ticket_line_set();
        
        $this->assertTrue(!empty($set));
    }
    
    /**
     * @test
     */
    public function is_lottery_minimum_lines_set()
    {
        $lottery_minimum_lines = $this->object->get_lottery_minimum_lines();
        
        parent::assertFalse(is_null($lottery_minimum_lines));
    }
    
    /**
     * @test
     */
    public function is_process_working_fine()
    {
        $this->markTestSkipped('Test needs work');
        $results_expected = [
            Forms_Wordpress_Bonuses_Ticket_Line::RESULT_OK,
            Forms_Wordpress_Bonuses_Ticket_Line::RESULT_WITH_ERRORS
        ];
        $result = $this->object->process_form();
        
        parent::assertTrue(in_array($result, $results_expected));
    }
}
