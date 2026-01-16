<?php

/**
 * Description of Forms_Whitelabel_Bonuses_WelcomeTest
 */
class Forms_Whitelabel_Bonuses_WelcomeTest extends Test_Feature
{

    /**
     * @var Forms_Whitelabel_Bonuses_Welcome
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
        
        if ($this->in_transaction) {
            DB::start_transaction();
            
            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment 
                FROM whitelabel_bonus";
            $result = DB::query($query)->execute();
            $this->start_auto_increment = $result[0]['current_auto_increment'];
        }
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $this->object = new Forms_Whitelabel_Bonuses_Welcome(
            Helpers_General::SOURCE_WHITELABEL,
            $this->whitelabel
        );
        
        $set = [
            'whitelabel_id' => (int)$this->whitelabel['id'],
            'bonus_id' => Forms_Whitelabel_Bonuses_Main::BONUS_WELCOME,
            'lottery_id' => 1,
        ];
        $whitelabel_bonus_model = Model_Whitelabel_Bonus::forge();
        $whitelabel_bonus_model->set($set);
        $whitelabel_bonus_model->save();
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
            
            $query = "ALTER TABLE whitelabel_bonus AUTO_INCREMENT = " . $this->start_auto_increment;
            DB::query($query)->execute();
        }
    }
    
    /**
     * @test
     */
    public function is_source_whitelabel_set()
    {
        $source = $this->object->get_source();
        parent::assertEquals(Helpers_General::SOURCE_WHITELABEL, $source);
    }
    
    /**
     * @test
     */
    public function is_get_inside_set()
    {
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Whitelabel_Bonuses_Welcome::class, $inside);
    }
    
    /**
     * @test
     */
    public function is_list_of_lotteries_availble()
    {
        $lotteries_to_compare = Model_Lottery::get_all_lotteries_for_whitelabel_short($this->whitelabel['id']);
        $lotteries = $this->object->get_list_of_lotteries();
        
        parent::assertEquals($lotteries_to_compare, $lotteries);
    }
    
    /**
     * @test
     */
    public function is_edit_data_set()
    {
        $whitelabel_bonus_model = $this->object->get_model_whitelabel_bonus();
        parent::assertInstanceOf(Model_Whitelabel_Bonus::class, $whitelabel_bonus_model);
    }
    
    /**
     *
     */
    public function is_bonus_set()
    {
        $bonus_saved = $this->object->save_bonus();
        
        $expected_results = [
            Forms_Whitelabel_Bonuses_Welcome::RESULT_OK,
            Forms_Whitelabel_Bonuses_Welcome::RESULT_WITH_ERRORS
        ];
        
        parent::assertTrue(in_array($bonus_saved, $expected_results));
    }
    
    /**
     * @test
     */
    public function is_max_lottery_id_set()
    {
        $this->object->get_list_of_lotteries();
        $max_lottery_id = $this->object->get_max_lottery_id();
                
        parent::assertGreaterThanOrEqual(0, $max_lottery_id);
    }
}
