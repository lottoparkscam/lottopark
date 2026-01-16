<?php

/**
 * Description of Forms_Whitelabel_ReportsTest
 */
class Forms_Whitelabel_ReportsTest extends Test_Feature
{

    /**
     * @var Forms_Whitelabel_Reports
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
        parent::setUp();
        
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);

        $this->markTestIncomplete('Error, test case need work');
        $this->object = new Forms_Whitelabel_Reports($this->whitelabel);
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * @test
     */
    public function is_countries_set()
    {
        $coutries = $this->object->get_countries();
        //var_dump($coutries);
        parent::assertFalse(empty($coutries));
    }
    
    /**
     * @test
     */
    public function is_languages_set()
    {
        $languages = $this->object->get_languages();
        //var_dump($languages);
        parent::assertFalse(empty($languages));
    }
    
    /**
     * @test
     */
    public function is_get_inside_set()
    {
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Whitelabel_Reports_Reports::class, $inside);
    }

    /**
     * @test
     */
    public function is_date_start_set()
    {
        $date_start_time = "07/01/2019";
        $result_date_start = $this->object->prepare_and_get_date_start_value($date_start_time);
        parent::assertFalse(empty($result_date_start));
    }
    
    /**
     * @test
     */
    public function is_date_end_set()
    {
        $date_end_time = "07/04/2019";
        $result_date_end = $this->object->prepare_and_get_date_end_value($date_end_time);
        parent::assertFalse(empty($result_date_end));
    }
    
    /**
     * @test
     */
    public function is_filter_add_set()
    {
        $language = "1";
        $country = "AF";
        $this->object->prepare_filters($language, $country);
        $fitler_add = $this->object->get_filter_add();
        
        parent::assertFalse(empty($fitler_add));
    }
    
    /**
     * @test
     */
    public function is_params_set()
    {
        $language = "1";
        $country = "AF";
        $this->object->prepare_filters($language, $country);
        $params = $this->object->get_params();
        
        parent::assertFalse(empty($params));
    }
    
    /**
     * @test
     */
    public function is_register_dates_data_set()
    {
        $date_start_time = "07/01/2019";
        $this->object->prepare_and_get_date_start_value($date_start_time);
        $date_end_time = "07/04/2019";
        $this->object->prepare_and_get_date_end_value($date_end_time);
        $register_dates_data = $this->object->prepare_dates_based_on_column_name("date_register");
        
        parent::assertFalse(empty($register_dates_data));
    }
    
    /**
     * @test
     */
    public function is_first_purchase_dates_data_set()
    {
        $date_start_time = "07/01/2019";
        $this->object->prepare_and_get_date_start_value($date_start_time);
        $date_end_time = "07/04/2019";
        $this->object->prepare_and_get_date_end_value($date_end_time);
        $first_purchase_dates_data = $this->object->prepare_dates_based_on_column_name("first_purchase");
        
        parent::assertFalse(empty($first_purchase_dates_data));
    }
    
    /**
     * @test
     */
    public function is_dates_data_set()
    {
        $date_start_time = "07/01/2019";
        $this->object->prepare_and_get_date_start_value($date_start_time);
        $date_end_time = "07/04/2019";
        $this->object->prepare_and_get_date_end_value($date_end_time);
        $dates_data = $this->object->prepare_dates_based_on_column_name("date");
        
        parent::assertFalse(empty($dates_data));
    }
    
    /**
     * @test
     */
    public function is_finance_dates_data_set()
    {
        $date_start_time = "07/01/2019";
        $this->object->prepare_and_get_date_start_value($date_start_time);
        $date_end_time = "07/04/2019";
        $this->object->prepare_and_get_date_end_value($date_end_time);
        $finance_dates_data = $this->object->prepare_dates_based_on_column_name("wut.date");
        parent::assertFalse(empty($finance_dates_data));
    }
    
    /**
     * @test
     */
    public function is_total_commissions_dates_data_set()
    {
        $date_start_time = "07/01/2019";
        $this->object->prepare_and_get_date_start_value($date_start_time);
        $date_end_time = "07/04/2019";
        $this->object->prepare_and_get_date_end_value($date_end_time);
        $total_commissions_dates_data = $this->object->prepare_dates_based_on_column_name("wt.date");
        parent::assertFalse(empty($total_commissions_dates_data));
    }
}
