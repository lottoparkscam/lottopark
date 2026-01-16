<?php

/**
 * Description of Presenter_Admin_Whitelabels_Settings
 */
class Presenter_Admin_Whitelabels_Settings extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Settings;
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->main_process();
    }
}
