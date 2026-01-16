<?php

/**
 * Description of Presenter_Whitelabel_Settings_Index
 */
class Presenter_Whitelabel_Settings_Index extends Presenter_Presenter
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
