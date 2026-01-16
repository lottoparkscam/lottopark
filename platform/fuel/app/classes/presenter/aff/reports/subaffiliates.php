<?php

/**
 * Prepare data for views/aff/reports/subaffiliates.
 *
 * @author Marcin
 */
class Presenter_Aff_Reports_Subaffiliates extends Presenter_Presenter
{
    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        $this->prepare_languages();
        $this->prepare_countries();
        
        // get and set selection tool for options
        $this->set_safe("get_selected", parent::closure_get_selected());

        // get and set tool for reading last value of text inputs
        $this->set_safe("get_last_text", parent::closure_get_last_text());

        // prepare data for table
        $this->prepare_subaffiliates();
    }

    /**
     * Prepare languages for select in filter.
     */
    private function prepare_languages()
    {
        // prepare languages for filter form
        $key = "languages";
        $languages = $this->get($key);
        // prepare languages with default entry
        $languages_prepared["a"] = _("Language");
        foreach ($languages as $language) {
            $languages_prepared[$language["id"]] = parent::prepare_language($language["code"]);
        }
        // set prepared languages
        $this->set($key, $languages_prepared);
    }
    
    /**
     * Prepare countries for select in filter.
     */
    private function prepare_countries()
    {
        // prepare countries
        $key = "countries";
        $countries = $this->get($key);
        // prepare countries with default entry
        $countries_prepared["a"] = _("Country");
        foreach ($countries as $code => $country) {
            $countries_prepared[$code] = Security::htmlentities($country);
        }
        // set prepared countries
        $this->set($key, $countries_prepared);
    }
    
    /**
     * Prepare sub-affiliates for table view.
     */
    private function prepare_subaffiliates()
    {
        // prepare subaffs for table
        $key = "subaffiliates";
        $subaffiliates = $this->get($key);
        $subaffiliates_prepared = null; // secure from undefined
        foreach ($subaffiliates as $subaffiliate) {
            $subaffiliates_prepared[$subaffiliate["id"]] = [
                Security::htmlentities($subaffiliate["token"]),
                $this->prepare_user_data($subaffiliate, _("Anonymous")),
                Security::htmlentities($subaffiliate["phone"]),
                parent::prepare_empty_country($subaffiliate["country"], $this->countries),
                parent::prepare_language($subaffiliate["lcode"]),
                Security::htmlentities($subaffiliate["timezone"]),
                parent::prepare_date($subaffiliate["date_created"]),
                $this->prepare_activity_data($subaffiliate),
            ];
        }
        // set prepared subaffiliates
        $this->set($key, $subaffiliates_prepared);
    }
    
    /**
     * Prepare user data in sub-affiliates.
     * Data is checked with htmlentities.
     * @param array $subaffiliate sub-affiliate data.
     * @param string $default_username default username, in case parent name and surname are empty.
     * @return string prepared user data, never empty or null.
     */
    private function prepare_user_data($subaffiliate, $default_username)
    {
        // get proper user name
        if (empty($subaffiliate["name"]) && empty($subaffiliate["surname"])) {
            $user_name = $default_username;
        } else {
            $user_name = "{$subaffiliate["name"]} {$subaffiliate["surname"]}";
        }
        // TODO: it maybe a good idea to write wrapper around entities and call it once over every suspicious field, instead of calling it everytime.
        $user_name = Security::htmlentities($user_name);
        $login = Security::htmlentities($subaffiliate["login"]);
        $email = Security::htmlentities($subaffiliate["email"]);
        // return rest of data
        $is_confirmed = parent::prepare_bool($subaffiliate["is_confirmed"]);
        return "$user_name &bull; $login <br> $is_confirmed $email";
    }
    
    /**
     * Return prepared activity data.
     * Data is checked with htmlentities.
     * @param array $subaffiliate subaffiliate.
     * @return string prepared data, never null.
     */
    private function prepare_activity_data($subaffiliate)
    {
        // set conditional breaks
        $break = !empty($subaffiliate["last_ip"]) ? "<br>" : ""; // make break only if second element is not  empty
        $break2 = !empty($subaffiliate["last_country"]) ? "<br>" : ""; // make break only if third element is not  empty
        // return proper activity data.
        $last_active = parent::prepare_date($subaffiliate["last_active"]);
        $last_ip = parent::prepare_nullable($subaffiliate["last_ip"]);
        $last_country = parent::prepare_nullable($subaffiliate["last_country"]);
        return "$last_active$break$last_ip$break2$last_country";
    }
}
