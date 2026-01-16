<?php

/**
 * Description of Presenter_Admin_Whitelabels_Settings_Currency_Edit
 */
class Presenter_Admin_Whitelabels_Settings_Currency_Edit extends Presenter_Presenter
{
    use Presenter_Traits_Currency_Edit;
    
    /**
     *
     * @var string
     */
    private $currency_start_url = "";
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->currency_start_url = "/whitelabels/settings_currency/" .
            $this->whitelabel['id'];

        $this->main_process();
    }
}
