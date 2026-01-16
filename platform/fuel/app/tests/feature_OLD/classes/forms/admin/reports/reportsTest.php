<?php

/**
 * Description of reports
 */
class Forms_Admin_Reports_ReportsTest extends Test_Feature
{
    /**
     * @var Forms_Admin_Reports_Reports
     */
    protected $object;

    private $date_start_time = "10/01/2019";

    private $date_end_time = "10/31/2019";

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->object = new Forms_Admin_Reports_Reports();
        $this->object->set_should_process(true);
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
        parent::assertFalse(empty($coutries));
    }

    /**
     * @test
     */
    public function is_languages_set()
    {
        $languages = $this->object->get_languages();
        parent::assertFalse(empty($languages));
    }

    /**
     * @test
     */
    public function is_get_inside_set()
    {
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Admin_Reports_Reports::class, $inside);
    }

    /**
     * @test
     */
    public function is_additional_rows_for_filters_set()
    {
        $additional_rows_for_filters = $this->object->get_additional_rows_for_filters();
        parent::assertFalse(empty($additional_rows_for_filters));
    }

    /**
     * @test
     */
    public function is_extended_whitelabels_list_is_set()
    {
        $this->markTestIncomplete('Error, test case need work');
        $result_extended_whitelabels_list = $this->object->get_extended_whitelabels_list();
        $this->assertFalse(empty($result_extended_whitelabels_list));
    }

    /**
     * @test
     */
    public function is_date_start_set()
    {
        $result_date_start = $this->object->prepare_and_get_date_start_value($this->date_start_time);
        parent::assertFalse(empty($result_date_start));
    }

    /**
     * @test
     */
    public function is_date_end_set()
    {
        $result_date_end = $this->object->prepare_and_get_date_end_value($this->date_end_time);
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
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $register_dates_data = $this->object->prepare_dates_based_on_column_name("date_register");

        parent::assertFalse(empty($register_dates_data));
    }

    /**
     * @test
     */
    public function is_first_purchase_dates_data_set()
    {
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $first_purchase_dates_data = $this->object->prepare_dates_based_on_column_name("first_purchase");

        parent::assertFalse(empty($first_purchase_dates_data));
    }

    /**
     * @test
     */
    public function is_dates_data_set()
    {
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $dates_data = $this->object->prepare_dates_based_on_column_name("date");

        parent::assertFalse(empty($dates_data));
    }

    /**
     * @test
     */
    public function is_finance_dates_data_set()
    {
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $finance_dates_data = $this->object->prepare_dates_based_on_column_name("wut.date");
        parent::assertFalse(empty($finance_dates_data));
    }

    /**
     * @test
     */
    public function is_total_commissions_dates_data_set()
    {
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $total_commissions_dates_data = $this->object->prepare_dates_based_on_column_name("wt.date");
        parent::assertFalse(empty($total_commissions_dates_data));
    }

    /**
     * @test
     */
    public function is_get_whitelabel_type_set()
    {
        $type_expected = Helpers_General::WHITELABEL_TYPE_V2;
        $whitelabel_extended = "all_v2";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $result_type = $this->object->get_whitelabel_type();

        parent::assertEquals($type_expected, $result_type);
    }

    /**
     * @test
     */
    public function is_manager_currency_set()
    {
        $currency_tab_expected = Helpers_Currency::get_mtab_currency(true, "EUR");

        $whitelabel_id = 1;
        $manager_currency_tab = $this->object->get_manager_currency_tab($whitelabel_id);

        parent::assertEquals($currency_tab_expected['code'], $manager_currency_tab['code']);
    }

    /**
     * @test
     */
    public function is_currency_tab_for_process_set()
    {
        $this->markTestIncomplete('Error, test case need work');
        $whitelabel_extended = "all";
        $this->object->set_whitelabel_id($whitelabel_extended);

        // System Currency Tab
        $currency_tab_expected = Helpers_Currency::get_mtab_currency(true, "USD");
        $currency_tab_for_process = $this->object->get_currency_tab_for_process();
        parent::assertEquals($currency_tab_expected, $currency_tab_for_process);
    }

    /**
     * @test
     */
    public function is_whitelabel_set()
    {
        $this->markTestIncomplete('Error, test case need work');
        $whitelabel_extended = "1";
        $this->object->set_whitelabel_id($whitelabel_extended);
        $whitelabel_id = $this->object->get_whitelabel_id();

        parent::assertEquals($whitelabel_extended, $whitelabel_id);
        $this->markTestIncomplete('TODO');
    }

    /**
     * @test
     */
    public function is_set_register_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);
        $this->markTestIncomplete('Error, test case need work');
        $result_register_count = $this->object->get_register_count();
        parent::assertFalse(is_null($result_register_count));
    }

    /**
     * @test
     */
    public function is_filter_add_register_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);
        $this->markTestIncomplete('Error, test case need work');
        $result_register_count = $this->object->get_register_count();

        $filter_add_register_count = $this->object->get_filter_add_register_count();
        parent::assertFalse(is_null($filter_add_register_count));
    }

    /**
     * @test
     */
    public function is_params_register_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);
        $this->markTestIncomplete('Error, test case need work');
        $result_register_count = $this->object->get_register_count();

        $params_register_count = $this->object->get_params_register_count();
        parent::assertFalse(is_null($params_register_count));
    }

    /**
     * @test
     */
    public function is_params_register_confirmed_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );
        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_register_confirmed_count = $this->object->get_register_confirmed_count();

        parent::assertFalse(is_null($result_register_confirmed_count));
    }

    /**
     * @test
     */
    public function is_active_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);
        $this->markTestIncomplete('Error, test case need work');
        $result_register_count = $this->object->get_register_count();

        $result_active_count = $this->object->get_active_count();

        parent::assertFalse(is_null($result_active_count));
    }

    /**
     * @test
     */
    public function is_first_time_deposit_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_first_time_deposit_count = $this->object->get_first_time_deposit_count();

        parent::assertFalse(is_null($result_first_time_deposit_count));
    }

    /**
     * @test
     */
    public function is_second_time_deposit_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_second_time_deposit_count = $this->object->get_second_time_deposit_count();

        parent::assertFalse(is_null($result_second_time_deposit_count));
    }

    /**
     * @test
     */
    public function is_first_time_purchase_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_first_time_purchase_count = $this->object->get_first_time_purchase_count();
        parent::assertFalse(is_null($result_first_time_purchase_count));
    }

    /**
     * @test
     */
    public function is_second_time_purchase_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_second_time_purchase_count = $this->object->get_second_time_purchase_count();

        parent::assertFalse(is_null($result_second_time_purchase_count));
    }

    /**
     * @test
     */
    public function is_tickets_paid_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_tickets_paid_count = $this->object->get_tickets_paid_count();

        parent::assertFalse(is_null($result_tickets_paid_count));
    }

    /**
     * @test
     */
    public function is_tickets_win_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_tickets_win_count = $this->object->get_tickets_win_count();

        parent::assertFalse(is_null($result_tickets_win_count));
    }

    /**
     * @test
     */
    public function is_tickets_win_sum_prize_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_tickets_win_sum_prize = $this->object->get_tickets_win_sum_prize();

        parent::assertFalse(is_null($result_tickets_win_sum_prize));
    }

    /**
     * @test
     */
    public function is_deposit_count_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );
        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_deposits_count = $this->object->get_deposits_count();

        $this->markTestIncomplete('Error, test case need work');
        parent::assertFalse(empty($result_deposits_count));
    }

    /**
     * @test
     */
    public function is_deposit_amount_manager_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "1";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_deposit_amount_manager = $this->object->get_deposit_amount_value();
        parent::assertFalse(is_null($result_deposit_amount_manager));
    }

    /**
     * @test
     */
    public function is_transactions_deposit_details_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_transactions_deposit_details = $this->object->get_transactions_deposit_details();

        parent::assertFalse(is_null($result_transactions_deposit_details));
    }

    /**
     * @test
     */
    public function is_prepare_bonus_cost_set()
    {
        $whitelabel_extended = "all";
        $this->object->prepare_and_get_date_start_value($this->date_start_time);
        $this->object->prepare_and_get_date_end_value($this->date_end_time);
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $bouns_cost_sum = $this->object->prepare_and_get_bonus_cost_sum();

        parent::assertFalse(is_null($bouns_cost_sum));
    }

    /**
     * @test
     */
    public function is_transactions_sale_amount_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_sale_amounts = $this->object->get_sale_amounts();

        parent::assertFalse(is_null($result_sale_amounts));
    }

    /**
     * @test
     */
    public function is_set_finance_data_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all_v1";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_finance_data = $this->object->get_finance_data();

        parent::assertFalse(is_null($result_finance_data));
    }

    /**
     * @test
     */
    public function is_commissions_manager_sum_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $result_commissions_manager_sum = $this->object->get_commissions_sum_value();
        parent::assertFalse(is_null($result_commissions_manager_sum));
    }

    /**
     * @test
     */
    public function is_payment_method_report_set()
    {
        $this->object->prepare_dates(
            $this->date_start_time,
            $this->date_end_time
        );

        $whitelabel_extended = "all";
        $this->object->set_whitelabel_type($whitelabel_extended);
        $this->object->set_whitelabel_id($whitelabel_extended);

        $this->markTestIncomplete('Error, test case need work');
        $result_payment_method_report = $this->object->set_payment_method_report();
        parent::assertFalse(is_null($result_payment_method_report));
    }
}
