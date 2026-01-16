<?php

/**
 * Description of Forms_Admin_Whitelabels_List
 */
class Forms_Admin_Whitelabels_List extends Forms_Main
{
    /**
     *
     * @var Presenter_Admin_Whitelabels_List
     */
    private $inside;
    
    /**
     *
     * @param string $path_to_view
     */
    public function __construct(string $path_to_view)
    {
        $this->inside = Presenter::forge($path_to_view);
    }
    
    /**
     *
     * @return \Presenter_Admin_Whitelabels_List
     */
    public function get_inside(): Presenter_Admin_Whitelabels_List
    {
        return $this->inside;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $whitelabels = Model_Whitelabel::find([
            "order_by" => ["id" => "ASC"]
        ]);

        if (empty($whitelabels)) {
            return self::RESULT_DB_ERROR;
        }

        $this->inside->set("whitelabels", $whitelabels);
        
        return self::RESULT_OK;
    }
}
