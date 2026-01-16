<?php

/**
 * Description of Forms_Admin_Whitelabels_ListTest
 */
class Forms_Admin_Whitelabels_ListTest extends Test_Unit
{

    /**
     * @var Forms_Admin_Whitelabels_List
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        $path_to_view = "admin/whitelabels/list";
        $this->object = new Forms_Admin_Whitelabels_List($path_to_view);
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
        parent::assertInstanceOf(Forms_Admin_Whitelabels_List::class, $this->object);
    }
     
    /**
     * @test
     */
    public function is_get_inside_set(): void
    {
        $inside = $this->object->get_inside();
        parent::assertInstanceOf(Presenter_Admin_Whitelabels_List::class, $inside);
    }
}
