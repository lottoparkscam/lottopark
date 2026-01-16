<?php

/**
 * Trait for preparation of first time purchases in presenter.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Presenter_Traits_Aff_Reports_Ftps
{

    /**
     * Prepare ftps.
     * @return array prepared ftps.
     */
    private function prepare_ftps(): array
    {
        $prepared_ftps = [];
        // go over every row of ftps and prepare them
        foreach ($this->ftpcount as $ftp) {
            // todo: prepare rest of data here, instead of view
            $ftp['lead_name'] = $this->prepare_lead_name($ftp);
            $ftp['lead_email'] = Security::htmlentities($ftp['lead_email']);
            $prepared_ftps[] = $ftp; // add new entry to prepared ftps
        }

        // return prepared values
        return $prepared_ftps;
    }
}
