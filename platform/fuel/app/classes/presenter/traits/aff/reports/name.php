<?php

/**
 * This trait allow to easily integrate prepare name from query functionality into specific presenters.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Presenter_Traits_Aff_Reports_Name
{

    /**
     * Prepare lead name.
     * @param array $row row containing name and surname.
     * @return string prepared name and surname.
     */
    public function prepare_lead_name(array $row): string
    {
        if (!$this->user['is_show_name']) {
            return ''; // nothing when disabled
        }
        
        if (empty($row['lead_name']) && empty($row['lead_surname'])) { // anonymous for name and surname empty
            $name = _('Anonymous');
        } else { // otherwise spaced name and surname
            $name = "{$row['lead_name']} {$row['lead_surname']}";
        }
        
        // return safe name
        return Security::htmlentities($name);
    }
}
