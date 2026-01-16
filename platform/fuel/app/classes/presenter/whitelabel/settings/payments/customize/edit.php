<?php

/**
 * Description of customize
 */
final class Presenter_Whitelabel_Settings_Payments_Customize_Edit extends Presenter_Presenter
{
    use Presenter_Traits_Payments_Customize_Edit;

    /**
     *
     * @var string
     */
    private $begin_url = '';
    
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $this->begin_url = '/paymentmethods/customize/' .
            $this->current_kmethod_idx;
        
        $this->main_process();
    }
}
