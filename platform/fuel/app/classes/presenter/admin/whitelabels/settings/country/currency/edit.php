<?php

/**
 * Description of Presenter_Admin_Whitelabels_Settings_Country_Currency_Edit
 */
class Presenter_Admin_Whitelabels_Settings_Country_Currency_Edit extends Presenter_Presenter
{
    use Presenter_Traits_Country_Currency_Edit;
    
    /**
     *
     * @var string
     */
    private $country_currency_start_url = "";
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->country_currency_start_url = "/whitelabels/settings_country_currency/" .
            $this->whitelabel['id'];
        
        $this->main_process();
    }
}
