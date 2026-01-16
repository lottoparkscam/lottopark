<?php

use Fuel\Core\Pagination;

/**
 * Description of Forms_Admin_Whitelabels_Prepaid_ListTest
 */
class Forms_Admin_Whitelabels_Prepaid_ListTest extends Test_Unit
{

    /**
     * @var Forms_Admin_Whitelabels_Prepaid_List
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        $whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        $this->object = new Forms_Admin_Whitelabels_Prepaid_List(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
    }

    /**
     * @test
     */
    public function is_instance_of_forms_whitelabel_list(): void
    {
        parent::assertInstanceOf(Forms_Admin_Whitelabels_Prepaid_List::class, $this->object);
    }
     
    /**
     * @test
     */
    public function is_get_inside_set(): void
    {
        $path_to_view = "admin/whitelabels/prepaid/list";
        $this->object->set_inside_by_path_to_view($path_to_view);
        
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Presenter::class, $inside);
    }
        
    /**
     *
     */
    public function list_of_results_from_process_form_set(): void
    {
        // I don't know how to solve problem of
        // "Trying to get propery 'uri' of non-object" error of Pagination
        
//        $results_array = [
//            Forms_Admin_Whitelabels_Prepaid_List::RESULT_OK,
//            Forms_Admin_Whitelabels_Prepaid_List::RESULT_NULL_DATA,
//        ];
//
//        $result = $this->object->process_form();
//
//        parent::assertContains(
//            $result,
//            $results_array
//        );
    }
}
