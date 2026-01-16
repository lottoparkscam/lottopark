<?php

/**
 * Description of Forms_Admin_Whitelabels_Prepaid_NewTest
 */
class Forms_Admin_Whitelabels_Prepaid_NewTest extends Test_Feature
{

    /**
     * @var Forms_Admin_Whitelabels_Prepaid_New
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
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        parent::setUp();
        
        if ($this->in_transaction) {
            DB::start_transaction();
            
            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment 
                FROM whitelabel_prepaid";
            $result = DB::query($query)->execute();
            $this->start_auto_increment = $result[0]['current_auto_increment'];
        }
        
        $whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $path_to_view = "admin/whitelabels/prepaid/new";
        $this->object = new Forms_Admin_Whitelabels_Prepaid_New($whitelabel, $path_to_view);
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
            
            $query = "ALTER TABLE whitelabel_prepaid AUTO_INCREMENT = " . $this->start_auto_increment;
            DB::query($query)->execute();
        }
    }

    /**
     * @test
     */
    public function is_get_inside_set()
    {
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Admin_Whitelabels_Prepaid_New::class, $inside);
    }

    /**
     * @test
     */
    public function is_adding_value_work_fine()
    {
        $statuses = [
            Forms_Admin_Whitelabels_Prepaid_New::RESULT_OK,
            Forms_Admin_Whitelabels_Prepaid_New::RESULT_WITH_ERRORS
        ];
        
        $prepaid_amount = "7.50";
        $result = $this->object->add_prepaid($prepaid_amount, false);
        parent::assertTrue(in_array($result, $statuses));
    }
    
    /**
     * @test
     */
    public function is_subtracting_value_work_fine()
    {
        $statuses = [
            Forms_Admin_Whitelabels_Prepaid_New::RESULT_OK,
            Forms_Admin_Whitelabels_Prepaid_New::RESULT_WITH_ERRORS
        ];
        
        $prepaid_amount = "10.00";
        $result = $this->object->subtract_prepaid($prepaid_amount, null, false);
        parent::assertTrue(in_array($result, $statuses));
    }
}
