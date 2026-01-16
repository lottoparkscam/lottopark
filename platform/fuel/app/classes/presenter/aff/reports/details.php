<?php

/**
 * Prepare data for views/aff/reports/details.
 *
 * @author Marcin
 */
class Presenter_Aff_Reports_Details extends Presenter_Presenter
{
    /**
     * This method will execute after controller action and before view rendering, so you can prepare necessary data here.
     */
    public function view()
    {
        // set custom empty value
        parent::set_empty_value(_("-"));
        
        $key = "details";
        // prepare details if they are valid
        $details = $this->get($key);
        if (!empty($details)) {
            $def_name = _("Anonymous");
            $details = [
                [Security::htmlentities(_("ID")), parent::prepare_empty_string(strtoupper($details["token"]))],
                [Security::htmlentities(_("Company")), parent::prepare_empty_string($details["company"])],
                [Security::htmlentities(_("First Name")), parent::prepare_empty_string_custom($details["name"], $def_name)],
                [Security::htmlentities(_("Last Name")), parent::prepare_empty_string_custom($details["surname"], $def_name)],
                [Security::htmlentities(_("Login")), Security::htmlentities($details["login"])],
                [Security::htmlentities(_("E-mail")), parent::prepare_bool($details["is_confirmed"]) . " " . Security::htmlentities($details["email"])],
                [Security::htmlentities(_("Country")), parent::prepare_empty_country($details["country"], $this->countries)],
                [Security::htmlentities(_("City")), parent::prepare_empty_string($details["city"])],
                [Security::htmlentities(_("Address #1")), parent::prepare_empty_string($details["address_1"])],
                [Security::htmlentities(_("Address #2")), parent::prepare_empty_string($details["address_2"])],
                [Security::htmlentities(_("Postal/ZIP Code")), parent::prepare_empty_string($details["zip"])],
                [Security::htmlentities(_("Birthdate")), parent::prepare_empty_date($details["birthdate"])],
                [Security::htmlentities(_("Phone")), parent::prepare_empty_phone($details["phone"], $details["phone_country"], $this->get("countries"))],
                [Security::htmlentities(_("Time Zone")), parent::prepare_empty_string($details["timezone"])],
                [Security::htmlentities(_("Language")), parent::prepare_empty_language($details["lcode"])],
                [Security::htmlentities(_("Date created")), parent::prepare_date($details["date_created"])],
                [Security::htmlentities(_("Last IP")), parent::prepare_empty_string($details["activity_data"])],
                [Security::htmlentities(_("Last Active")), parent::prepare_empty_date($details["last_active"])],
                [Security::htmlentities(_("Lead lifetime")), Security::htmlentities(parent::get_lifetimes(_("unlimited"))[$this->user['aff_lead_lifetime']])],
            ];
            // set prepared details
            $this->set($key, $details);
        }
    }
}
