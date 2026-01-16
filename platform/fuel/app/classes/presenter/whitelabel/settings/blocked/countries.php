<?php

/**
 * Prepare data for views/whitelabel/settings/blocked/countries
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
class Presenter_Whitelabel_Settings_Blocked_Countries extends Presenter_Presenter
{

    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        // prepare and set countries to view
        parent::set('blocked_countries', $this->prepare_blocked_countries());
    }

    /**
     * Prepare blocked countries.
     * @return array prepared blocked countries.
     */
    private function prepare_blocked_countries() : array
    {
        // set default value for undefined countries
        parent::set_empty_value(_('Undefined country.'));
        $blocked_countries = [];
        foreach ($this->blocked_countries as $country) {
            $blocked_countries[] = [
                'code' => $country['code'], // store code
                'name' => parent::prepare_empty_country($country['code'], $this->countries), // obtain name from countries
                'is_deletable' => $country['is_deletable']
            ];
        }
        return $blocked_countries;
    }
}
