<?php

/**
 * Prepare data for views/whitelabel/affs/new.
 *
 * @author Marcin
 */
class Presenter_Whitelabel_Affs_New extends Presenter_Presenter
{

    use Presenter_Traits_Whitelabel_Affs_Input;

    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $this->prepare_input_fields();
    }
}
