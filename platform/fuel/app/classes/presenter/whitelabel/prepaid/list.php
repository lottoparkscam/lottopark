<?php

/**
 * Description of Presenter_Admin_Whitelabels_Prepaid_List
 */
class Presenter_Whitelabel_Prepaid_List extends Presenter_Presenter
{
    use Presenter_Traits_Admin_Prepaid_List;
    
    /**
     *
     * @var string
     */
    private $start_url = "";
    
    /**
     *
     * @var int
     */
    private $source = null;
    
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $this->start_url = "";
        $this->source = Helpers_General::SOURCE_WHITELABEL;
        $this->main_process();
    }
}
