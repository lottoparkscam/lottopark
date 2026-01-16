<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_Stripe
 */
final class Forms_Whitelabel_Payment_Stripe extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @return array|null
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @param array $whitelabel
     * @return \Forms_Whitelabel_Payment_Stripe
     */
    public function set_whitelabel(array $whitelabel = null): Forms_Whitelabel_Payment_Stripe
    {
        $this->whitelabel = $whitelabel;
        return $this;
    }

    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("stripe");

        // Based on Stripe values of keys from Dashboard it seems that key should be
        // 42 chars long, but I allow longer strings - maybe it should be changed
        // back to 42
        
        $validation->add("input.stripe_publishable_key", _("Stripe Publishable Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);

        $validation->add("input.stripe_security_key", _("Stripe Security Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);
        
        $validation->add("input.stripe_signing_secret", _("Signing secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);
        
        $validation->add("input.stripe_userid_vendorid_metadata", _("Collect userId and vendorId metadata"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.stripe_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }
    
    /**
     *
     * @return string
     */
    public function get_confirmation_url(): string
    {
        $confirm_url = "";
        if (Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
            $confirm_url = '<strong>https://whitelotto.com/order/confirm/' .
                '{payment_id}</strong>. ';
        } else {
            $confirm_url = '<strong>https://' . $this->whitelabel['domain'] .
                Helper_Route::ORDER_CONFIRM .
                Helpers_Payment_Method::STRIPE_URI .
                '/{payment_id}</strong>. ';
        }
        
        return $confirm_url;
    }
    
    /**
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $stripe = [];
        
        $stripe_publishable_key_error_class = '';
        if (isset($errors['input.stripe_publishable_key'])) {
            $stripe_publishable_key_error_class = ' has-error';
        }
        $stripe['publishable_key_error_class'] = $stripe_publishable_key_error_class;
        
        $stripe_publishable_key_value_prepared = '';
        if (null !== Input::post("input.stripe_publishable_key")) {
            $stripe_publishable_key_value_prepared = Input::post("input.stripe_publishable_key");
        } elseif (isset($data['stripe_publishable_key'])) {
            $stripe_publishable_key_value_prepared = $data['stripe_publishable_key'];
        }
        $stripe['publishable_key_value'] = Security::htmlentities($stripe_publishable_key_value_prepared);

        $stripe_security_key_error_class = '';
        if (isset($errors['input.stripe_security_key'])) {
            $stripe_security_key_error_class = ' has-error';
        }
        $stripe['security_key_error_class'] = $stripe_security_key_error_class;
        
        $stripe_security_key_value_prepared = '';
        if (null !== Input::post("input.stripe_security_key")) {
            $stripe_security_key_value_prepared = Input::post("input.stripe_security_key");
        } elseif (isset($data['stripe_security_key'])) {
            $stripe_security_key_value_prepared = $data['stripe_security_key'];
        }
        $stripe['security_key_value'] = Security::htmlentities($stripe_security_key_value_prepared);

        $stripe_signing_secret_error_class = '';
        if (isset($errors['input.stripe_signing_secret'])) {
            $stripe_signing_secret_error_class = ' has-error';
        }
        $stripe['signing_secret_error_class'] = $stripe_signing_secret_error_class;
        
        $stripe_signing_secret_value_prepared = '';
        if (null !== Input::post("input.stripe_signing_secret")) {
            $stripe_signing_secret_value_prepared = Input::post("input.stripe_signing_secret");
        } elseif (isset($data['stripe_signing_secret'])) {
            $stripe_signing_secret_value_prepared = $data['stripe_signing_secret'];
        }
        $stripe['signing_secret_value'] = Security::htmlentities($stripe_signing_secret_value_prepared);

        $stripe['publishable_key_info'] = _(
            "You can find that value by choosing <strong>Developers</strong> " .
            "menu and next choosing <strong>API keys</strong> submenu.<br> " .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>View test data</strong> option disabled in the main menu!"
        );

        $stripe['security_key_info'] = _(
            "You can find that value by choosing <strong>Developers</strong> " .
            "menu and next choosing <strong>API keys</strong> submenu.<br> " .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>View test data</strong> option disabled in the main menu!"
        );

        $stripe_signing_secret_info_part_one = _(
            "You can find that value by choosing <strong>Developers</strong> " .
            "menu and then choosing <strong>Webhooks</strong> submenu.<br> " .
            "First you have to add an endpoint to the list by clicking " .
            "'<strong>+Add endpoint</strong>' button on the top right. " .
            "Please enter URL string within <strong>Endpoint URL</strong> as "
        );

        $stripe_signing_secret_info_part_two = _(
            "Choose the value <strong>checkout.session.completed</strong> from " .
            "<strong>Events to send</strong> list. " .
            "Next, click <strong>Add endopoint</strong> button to submit request. " .
            "After that you will see an entry on the list. Now you can click that " .
            "and you will see full data. Try to find <strong>Signing secret</strong> panel " .
            "and <strong>Click to reveal</strong> button. You have to copy the shown value.<br>" .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>View test data</strong> option disabled in the main menu!"
        );

        $confirmation_url = $this->get_confirmation_url();

        $stripe_signing_secret_info = $stripe_signing_secret_info_part_one;
        $stripe_signing_secret_info .= $confirmation_url;
        $stripe_signing_secret_info .= $stripe_signing_secret_info_part_two;
        $stripe['signing_secret_info'] = $stripe_signing_secret_info;
        
        $stripe_userid_vendorid_metadata_checked = '';
        if ((null !== Input::post("input.stripe_userid_vendorid_metadata") &&
                (int)Input::post("input.stripe_userid_vendorid_metadata") === 1) ||
            (isset($data['stripe_userid_vendorid_metadata']) &&
                (int)$data['stripe_userid_vendorid_metadata'] === 1)
        ) {
            $stripe_userid_vendorid_metadata_checked = ' checked="checked"';
        }
        $stripe['userid_vendorid_metadata_checked'] = $stripe_userid_vendorid_metadata_checked;
        
        $stripe_userid_vendorid_metadata_description = _(
            "You have to send userId, vendorId and stripe_metadata=1 " .
            "values as GET parameters within the URL."
        );
        $stripe['userid_vendorid_metadata_description'] = $stripe_userid_vendorid_metadata_description;
        
        $stripe_test_checked = '';
        if ((null !== Input::post("input.stripe_test") &&
                (int)Input::post("input.stripe_test") === 1) ||
            (isset($data['stripe_test']) &&
                (int)$data['stripe_test'] === 1)
        ) {
            $stripe_test_checked = ' checked="checked"';
        }
        $stripe['test_checked'] = $stripe_test_checked;
        
        return $stripe;
    }
    
    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['stripe_publishable_key'] = $additional_values_validation->validated("input.stripe_publishable_key");
        $data['stripe_security_key'] = $additional_values_validation->validated("input.stripe_security_key");
        $data['stripe_signing_secret'] = $additional_values_validation->validated("input.stripe_signing_secret");
        $data['stripe_userid_vendorid_metadata'] = $additional_values_validation->validated("input.stripe_userid_vendorid_metadata") == 1 ? 1 : 0;
        $data['stripe_test'] = $additional_values_validation->validated("input.stripe_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
