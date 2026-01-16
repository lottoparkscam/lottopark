<?php

/**
 * Description of Forms_Wordpress_TicketTest
 */
class Forms_Wordpress_Bonuses_Ticket_TicketTest extends Test_Feature
{
    /**
     * @var Forms_Wordpress_Bonuses_Ticket_Ticket
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

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Need to be fixed');
        if ($this->in_transaction) {
            DB::start_transaction();
            
            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment 
                FROM whitelabel_user_ticket";
            $result = DB::query($query)->execute();
            $this->start_auto_increment = $result[0]['current_auto_increment'];
        }
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        Lotto_Settings::getInstance()->set('whitelabel', $this->whitelabel);
        $user_id = 1;
        $user = Model_Whitelabel_User::get_user_with_currencies_by_id_and_whitelabel(
            $user_id,
            $this->whitelabel
        );

        $lottery_id = 1;
        $lottery = Model_Lottery::get_single_row_by_id($lottery_id);
        
        $this->object = new Forms_Wordpress_Bonuses_Ticket_Ticket(
            $this->whitelabel,
            $user,
            $lottery
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
            
            $query = "ALTER TABLE whitelabel_user_ticket AUTO_INCREMENT = " . $this->start_auto_increment;
            DB::query($query)->execute();
        }
    }
    
    /**
     * @test
     */
    public function is_generated_token_set()
    {
        $token = $this->object->get_token();
        parent::assertFalse(empty($token));
    }
    
    /**
     * @test
     */
    public function is_user_currency_tab_set()
    {
        $result = $this->object->get_user_currency_tab();
        parent::assertTrue(is_array($result));
    }
    
    /**
     * @test
     */
    public function is_lottery_currency_tab_set()
    {
        $result = $this->object->get_lottery_currency_tab();
        parent::assertTrue(is_array($result));
    }
    
    /**
     * @test
     */
    public function is_system_currency_tab_set()
    {
        $result = $this->object->get_system_currency_tab();
        parent::assertTrue(is_array($result));
    }
    
    /**
     * @test
     */
    public function is_manager_currency_tab_set()
    {
        $result = $this->object->get_manager_currency_tab();
        parent::assertTrue(is_array($result));
    }
    
    /**
     * @test
     */
    public function is_ticket_draw_date_set()
    {
        $this->markTestSkipped('Need to be fixed');
        $draw_date = $this->object->get_ticket_draw_date();
        parent::assertFalse(empty($draw_date));
    }
    
    /**
     * @test
     */
    public function is_lottery_type_set()
    {
        $this->object->get_ticket_draw_date();
        $lottery_type = $this->object->get_lottery_type();
        
        parent::assertTrue(is_array($lottery_type));
    }
    
    /**
     * @test
     */
    public function is_lottery_model_set()
    {
        $lottery_models = [
            Helpers_General::LOTTERY_MODEL_PURCHASE,
            Helpers_General::LOTTERY_MODEL_MIXED,
            Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN,
            Helpers_General::LOTTERY_MODEL_NONE
        ];
        $lottery_model = $this->object->get_lottery_model();
        
        parent::assertTrue(in_array($lottery_model, $lottery_models));
    }
    
    /**
     * @test
     */
    public function is_should_insured_set()
    {
        $result = $this->object->get_should_insure();
        parent::assertTrue(in_array($result, [true, false]));
    }
    
    /**
     * @test
     */
    public function is_is_insured_set()
    {
        $result = $this->object->get_is_insured();
        
        parent::assertTrue(in_array($result, [true, false]));
    }
    
    /**
     * @test
     */
    public function is_tier_set()
    {
        $result = $this->object->get_tier();
        
        parent::assertGreaterThanOrEqual(0, $result);
    }
    
    /**
     * @test
     */
    public function is_price_lottery_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        parent::assertFalse(is_nan($price_lottery));
    }
    
    /**
     * @test
     */
    public function is_price_usd_set()
    {
        $price_usd = $this->object->get_price_usd();
        parent::assertFalse(is_nan($price_usd));
    }
    
    /**
     * @test
     */
    public function is_price_user_set()
    {
        $price_user = $this->object->get_price_user();
        parent::assertFalse(is_null($price_user));
    }
    
    /**
     * @test
     */
    public function is_price_of_ticket_set()
    {
        $price_of_ticket = $this->object->get_price_of_ticket();
        parent::assertCount(3, $price_of_ticket);
    }
    
    /**
     * @test
     */
    public function is_cost_lottery_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        parent::assertFalse(is_nan($cost_lottery));
    }
    
    /**
     * @test
     */
    public function is_cost_lottery_formatted_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_lottery_formatted = $this->object->get_cost_lottery_formatted($cost_lottery);
        parent::assertFalse(is_nan($cost_lottery_formatted));
    }
    
    /**
     * @test
     */
    public function is_cost_usd_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        parent::assertFalse(is_nan($cost_usd));
    }
    
    /**
     * @test
     */
    public function is_cost_usd_formatted_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_usd_formatted = $this->object->get_cost_usd_formatted($cost_usd);
        parent::assertFalse(is_nan($cost_usd_formatted));
    }
    
    /**
     * @test
     */
    public function is_cost_manager_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        parent::assertFalse(is_nan($cost_manager));
    }
    
    /**
     * @test
     */
    public function is_cost_manager_formatted_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_manager_formatted = $this->object->get_cost_manager_formatted($cost_manager);
        parent::assertFalse(is_nan($cost_manager_formatted));
    }
    
    /**
     * @test
     */
    public function is_cost_user_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_user = $this->object->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        parent::assertFalse(is_nan($cost_user));
    }
    
    /**
     * @test
     */
    public function is_cost_user_formatted_set()
    {
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_user = $this->object->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        $cost_user_formatted = $this->object->get_cost_user_formatted($cost_user);
        parent::assertFalse(is_nan($cost_user_formatted));
    }
    
    /**
     * @test
     */
    public function is_income_value_set()
    {
        $income_value_formatted = $this->object->get_income_value_formatted();
        parent::assertFalse(is_nan($income_value_formatted));
    }
    
    /**
     * @test
     */
    public function is_income_type_set()
    {
        $possible_results = [
            Helpers_General::LOTTERY_INCOME_TYPE_CURRENCY,
            Helpers_General::LOTTERY_INCOME_TYPE_PERCENT
        ];
        $income_type = $this->object->get_income_type();
        parent::assertTrue(in_array($income_type, $possible_results));
    }
    
    /**
     * @test
     */
    public function is_income_lottery_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        $cost_lottery = $this->object->get_cost_lottery();
        $income_lottery = $this->object->get_income_lottery(
            $price_lottery,
            $cost_lottery
        );
        parent::assertFalse(is_nan($income_lottery));
    }
    
    /**
     * @test
     */
    public function is_income_lottery_formatted_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        $cost_lottery = $this->object->get_cost_lottery();
        $income_lottery = $this->object->get_income_lottery(
            $price_lottery,
            $cost_lottery
        );
        $income_lottery_formatted = $this->object->get_income_lottery_formatted($income_lottery);
        parent::assertFalse(is_nan($income_lottery_formatted));
    }
    
    /**
     * @test
     */
    public function is_income_usd_set()
    {
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        parent::assertFalse(is_null($income_usd));
    }
    
    /**
     * @test
     */
    public function is_income_usd_formatted_set()
    {
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        $income_usd_formatted = $this->object->get_income_usd_formatted($income_usd);
        parent::assertFalse(is_null($income_usd_formatted));
    }
    
    /**
     * @test
     */
    public function is_income_user_set()
    {
        $price_user = $this->object->get_price_user();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_user = $this->object->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        $income_user = $this->object->get_income_user($price_user, $cost_user);
        parent::assertFalse(is_null($income_user));
    }
    
    /**
     * @test
     */
    public function is_income_user_formatted_set()
    {
        $price_user = $this->object->get_price_user();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_user = $this->object->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        $income_user = $this->object->get_income_user($price_user, $cost_user);
        $income_user_formatted = $this->object->get_income_user_formatted($income_user);
        parent::assertFalse(is_null($income_user_formatted));
    }
    
    /**
     * @test
     */
    public function is_income_manager_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_lottery = $this->object->get_income_lottery(
            $price_lottery,
            $cost_lottery
        );
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        $income_manager = $this->object->get_income_manager($income_lottery, $income_usd);
        parent::assertFalse(is_null($income_manager));
    }
    
    /**
     * @test
     */
    public function is_income_manager_formatted_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_lottery = $this->object->get_income_lottery(
            $price_lottery,
            $cost_lottery
        );
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        $income_manager = $this->object->get_income_manager($income_lottery, $income_usd);
        $income_manager_formatted = $this->object->get_income_manager_formatted($income_manager);
        parent::assertFalse(is_null($income_manager_formatted));
    }
    
    /**
     * @test
     */
    public function is_margin_value_set()
    {
        $margin_value = $this->object->get_margin_value();
        parent::assertFalse(is_null($margin_value));
    }
    
    /**
     * @test
     */
    public function is_margin_value_percentage_set()
    {
        $margin_value_percentage = $this->object->get_margin_value_percentage();
        parent::assertFalse(is_null($margin_value_percentage));
    }
    
    /**
     * @test
     */
    public function is_margin_lottery_formatted_set()
    {
        $price_lottery = $this->object->get_price_lottery();
        $cost_lottery = $this->object->get_cost_lottery();
        $income_lottery = $this->object->get_income_lottery(
            $price_lottery,
            $cost_lottery
        );
        $margin_value_percentage = $this->object->get_margin_value_percentage();

        $margin_lottery_formatted = $this->object->get_margin_lottery_formatted(
            $income_lottery,
            $margin_value_percentage
        );
        parent::assertFalse(is_null($margin_lottery_formatted));
    }
    
    /**
     * @test
     */
    public function is_margin_usd_formatted_set()
    {
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        $margin_value_percentage = $this->object->get_margin_value_percentage();
        $margin_usd_formatted = $this->object->get_margin_usd_formatted(
            $income_usd,
            $margin_value_percentage
        );
        
        parent::assertFalse(is_null($margin_usd_formatted));
    }
    
    /**
     * @test
     */
    public function is_margin_user_formatted_set()
    {
        $price_user = $this->object->get_price_user();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $cost_manager = $this->object->get_cost_manager($cost_lottery, $cost_usd);
        $cost_user = $this->object->get_cost_user($cost_lottery, $cost_usd, $cost_manager);
        $income_user = $this->object->get_income_user($price_user, $cost_user);
        $margin_value_percentage = $this->object->get_margin_value_percentage();
        $margin_user_formatted = $this->object->get_margin_user_formatted(
            $income_user,
            $margin_value_percentage
        );
        parent::assertFalse(is_null($margin_user_formatted));
    }
    
    /**
     * @test
     */
    public function is_margin_manager_formatted_set()
    {
        $price_usd = $this->object->get_price_usd();
        $cost_lottery = $this->object->get_cost_lottery();
        $cost_usd = $this->object->get_cost_usd($cost_lottery);
        $income_usd = $this->object->get_income_usd($price_usd, $cost_usd);
        $margin_value_percentage = $this->object->get_margin_value_percentage();
        $margin_usd_formatted = $this->object->get_margin_usd_formatted(
            $income_usd,
            $margin_value_percentage
        );
        $margin_manager_formatted = $this->object->get_margin_manager_formatted($margin_usd_formatted);
        parent::assertFalse(is_null($margin_manager_formatted));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_lottery_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        parent::assertFalse(is_nan($bonus_cost_lottery));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_lottery_formatted_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_lottery_formatted = $this->object->get_bonus_cost_lottery_formatted($bonus_cost_lottery);
        parent::assertFalse(is_nan($bonus_cost_lottery_formatted));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_usd_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_usd = $this->object->get_bonus_cost_usd($bonus_cost_lottery);
        parent::assertFalse(is_nan($bonus_cost_usd));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_usd_formatted_set()
    {
        $bonsu_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonsu_cost_usd = $this->object->get_bonus_cost_usd($bonsu_cost_lottery);
        $bonus_cost_usd_formatted = $this->object->get_bonus_cost_usd_formatted($bonsu_cost_usd);
        parent::assertFalse(is_nan($bonus_cost_usd_formatted));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_manager_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_usd = $this->object->get_bonus_cost_usd($bonus_cost_lottery);
        $bonus_cost_manager = $this->object->get_bonus_cost_manager(
            $bonus_cost_lottery,
            $bonus_cost_usd
        );
        parent::assertFalse(is_nan($bonus_cost_manager));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_manager_formatted_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_usd = $this->object->get_bonus_cost_usd($bonus_cost_lottery);
        $bonus_cost_manager = $this->object->get_bonus_cost_manager(
            $bonus_cost_lottery,
            $bonus_cost_usd
        );
        $bonus_cost_manager_formatted = $this->object->get_bonus_cost_manager_formatted($bonus_cost_manager);
        parent::assertFalse(is_nan($bonus_cost_manager_formatted));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_user_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_usd = $this->object->get_bonus_cost_usd($bonus_cost_lottery);
        $bonus_cost_manager = $this->object->get_bonus_cost_manager(
            $bonus_cost_lottery,
            $bonus_cost_usd
        );
        $bonus_cost_user = $this->object->get_bonus_cost_user(
            $bonus_cost_lottery,
            $bonus_cost_usd,
            $bonus_cost_manager
        );
        parent::assertFalse(is_nan($bonus_cost_user));
    }
    
    /**
     * @test
     */
    public function is_bonus_cost_user_formatted_set()
    {
        $bonus_cost_lottery = $this->object->get_bonus_cost_lottery();
        $bonus_cost_usd = $this->object->get_bonus_cost_usd($bonus_cost_lottery);
        $bonus_cost_manager = $this->object->get_bonus_cost_manager(
            $bonus_cost_lottery,
            $bonus_cost_usd
        );
        $bonus_cost_user = $this->object->get_bonus_cost_user(
            $bonus_cost_lottery,
            $bonus_cost_usd,
            $bonus_cost_manager
        );
        $bonus_cost_user_formatted = $this->object->get_bonus_cost_user_formatted($bonus_cost_user);
        parent::assertFalse(is_nan($bonus_cost_user_formatted));
    }
    
    /**
     * @test
     */
    public function is_ip_set()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $ip = $this->object->get_ip();
        parent::assertFalse(empty($ip));
    }
    
    /**
     * @test
     */
    public function is_prepare_ticket_fine()
    {
        $prepared_ticket = $this->object->get_prepared_ticket_set();
        parent::assertFalse(empty($prepared_ticket));
    }
    
    /**
     * @test
     */
    public function is_process_work()
    {
        $results = [
            Forms_Wordpress_Bonuses_Ticket_Ticket::RESULT_OK,
            Forms_Wordpress_Bonuses_Ticket_Ticket::RESULT_WITH_ERRORS
        ];
        
        $result_ticket_set = $this->object->process_form();
        
        parent::assertTrue(in_array($result_ticket_set, $results));
    }
}
