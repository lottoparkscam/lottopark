<?php

/**
 * Archetype for presenter handling subaff views.
 *
 * @author Marcin
 */
abstract class Presenter_Aff_Reports_Subaffs extends Presenter_Presenter
{
    /**
     * Prepare data for subaffs.
     * @param view $view child view reference.
     */
    protected function prepare_for_subaffs($view)
    {
        // set condition for displaying subaff filter
        $view->set("is_subaff", $view->reports_type === Controller_Aff::SUBAFFILIATE_REPORTS);
        
        // check if there is need to prepare for subaffs
        if (!$view->is_subaff) {
            return;
        }

        // prepare subaffs for filter
        $view->set("subaffs", $this->prepare_subaffs($view->subaffs));

        // get and set selection tool for options
        $view->set_safe("get_selected_extended", parent::closure_get_selected_extended());
    }
    
    /**
     * Prepare subaffs for select in filter.
     * @param array unprepared subaffs.
     * @return array prepared subaffs.
     */
    private function prepare_subaffs($subaffs)
    {
        // prepare subaffs with default entry
        $subaffs_prepared["a"] = _("Sub-affiliate");
        foreach ($subaffs as $subaff) {
            $subaffs_prepared[$subaff["id"]] = Security::htmlentities($this->prepare_subaff($subaff));
        }
        // set prepared subaffs
        return $subaffs_prepared;
    }
    
    /**
     * Prepare subaff for filter.
     * @param array $subaff subaff.
     * @return string prepared subaff, NO SECURITY.
     */
    private function prepare_subaff($subaff)
    {
        // get proper user name
        if (empty($subaff["name"]) && empty($subaff["surname"])) {
            $user_name = _("anonymous");
        } else {
            $user_name = "{$subaff["name"]} {$subaff["surname"]}";
        }
        // add mail and return
        return "$user_name &bull; {$subaff["login"]}";
    }
}
