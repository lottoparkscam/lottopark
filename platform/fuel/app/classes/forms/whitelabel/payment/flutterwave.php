<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_Flutterwave
 */
final class Forms_Whitelabel_Payment_Flutterwave extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("flutterwave");

        // I AM NOT QUITE SURE ABOUT THAT WHAT IS BELOW IN THE COMMENT
        // BECAUSE IT WAS COPIED FROM STRIPE CLASS
        //
        // Based on Flutterwave values of keys from Dashboard it seems that key should be
        // 42 chars long, but I allow longer strings - maybe it should be changed
        // back to 42
        
        $validation->add("input.flutterwave_public_key", _("Flutterwave Publishable Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);

        $validation->add("input.flutterwave_secret_key", _("Flutterwave Security Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);
        
        $validation->add("input.flutterwave_secret_webhook_key", _("Flutterwave secret webhook key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 70);

        $validation->add("input.flutterwave_payment_options", _("Flutterwave payment options"))
            ->add_rule("trim")
//            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]) // TODO: validation for ['card', 'mobile']
            ->add_rule("max_length", 150);

        $validation->add("input.flutterwave_network", _("Flutterwave network"))
            ->add_rule("trim")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 15);

        $validation->add("input.flutterwave_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1); //TODO: block prod if testing key is set

        return $validation;
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
        $flutterwave = [];
        
        // Public key
        $flutterwave_public_key_error_class = '';
        if (isset($errors['input.flutterwave_public_key'])) {
            $flutterwave_public_key_error_class = ' has-error';
        }
        $flutterwave['public_key_error_class'] = $flutterwave_public_key_error_class;
        
        $flutterwave_public_key_value_prepared = '';
        if (null !== Input::post("input.flutterwave_public_key")) {
            $flutterwave_public_key_value_prepared = Input::post("input.flutterwave_public_key");
        } elseif (isset($data['flutterwave_public_key'])) {
            $flutterwave_public_key_value_prepared = $data['flutterwave_public_key'];
        }
        $flutterwave['public_key_value'] = Security::htmlentities($flutterwave_public_key_value_prepared);

        // Secret key
        $flutterwave_secret_key_error_class = '';
        if (isset($errors['input.flutterwave_secret_key'])) {
            $flutterwave_secret_key_error_class = ' has-error';
        }
        $flutterwave['secret_key_error_class'] = $flutterwave_secret_key_error_class;
        
        $flutterwave_secret_key_value_prepared = '';
        if (null !== Input::post("input.flutterwave_secret_key")) {
            $flutterwave_secret_key_value_prepared = Input::post("input.flutterwave_secret_key");
        } elseif (isset($data['flutterwave_secret_key'])) {
            $flutterwave_secret_key_value_prepared = $data['flutterwave_secret_key'];
        }
        $flutterwave['secret_key_value'] = Security::htmlentities($flutterwave_secret_key_value_prepared);

        // Secret webhook key
        $flutterwave_secret_webhook_key_error_class = '';
        if (isset($errors['input.flutterwave_secret_webhook_key'])) {
            $flutterwave_secret_webhook_key_error_class = ' has-error';
        }
        $flutterwave['secret_webhook_key_error_class'] = $flutterwave_secret_webhook_key_error_class;
        
        $flutterwave_secret_webhook_key_value_prepared = '';
        if (null !== Input::post("input.flutterwave_secret_webhook_key")) {
            $flutterwave_secret_webhook_key_value_prepared = Input::post("input.flutterwave_secret_webhook_key");
        } elseif (isset($data['flutterwave_secret_webhook_key'])) {
            $flutterwave_secret_webhook_key_value_prepared = $data['flutterwave_secret_webhook_key'];
        }
        $flutterwave['secret_webhook_key_value'] = Security::htmlentities($flutterwave_secret_webhook_key_value_prepared);

        // Payment options
        $flutterwave_payment_options_error_class = '';
        if (isset($errors['input.flutterwave_payment_options'])) {
            $flutterwave_payment_options_error_class = ' has-error';
        }
        $flutterwave['payment_options_error_class'] = $flutterwave_payment_options_error_class;
        
        $flutterwave_payment_options_value_prepared = '';
        if (null !== Input::post("input.flutterwave_payment_options")) {
            $flutterwave_payment_options_value_prepared = Input::post("input.flutterwave_payment_options");
        } elseif (isset($data['flutterwave_payment_options'])) {
            $flutterwave_payment_options_value_prepared = $data['flutterwave_payment_options'];
        }
        $flutterwave['payment_options_value'] = Security::htmlentities($flutterwave_payment_options_value_prepared);

        // Network
        $flutterwave_network_error_class = '';
        if (isset($errors['input.flutterwave_network'])) {
            $flutterwave_network_error_class = ' has-error';
        }
        $flutterwave['network_error_class'] = $flutterwave_network_error_class;
        
        $flutterwave_network_value_prepared = '';
        if (null !== Input::post("input.flutterwave_network")) {
            $flutterwave_network_value_prepared = Input::post("input.flutterwave_network");
        } elseif (isset($data['flutterwave_network'])) {
            $flutterwave_network_value_prepared = $data['flutterwave_network'];
        }
        $flutterwave['network_value'] = Security::htmlentities($flutterwave_network_value_prepared);

        $flutterwave['public_key_info'] = _(
            "You can find that value by choosing <strong>Dashboard</strong> " .
            "menu and next choosing <strong>Settings->API keys</strong> submenu.<br> " .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>Test mode</strong> option disabled in the main menu!"
        );

        $flutterwave['secret_key_info'] = _(
            "You can find that value by choosing <strong>Dashboard</strong> " .
            "menu and next choosing <strong>Settings->API keys</strong> submenu.<br> " .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>Test mode</strong> option disabled in the main menu!"
        );

        $flutterwave['secret_webhook_key_info'] = _(
            "You can find that value by choosing <strong>Dashboard</strong> " .
            "menu and then choosing <strong>Settings->Webhooks</strong> submenu.<br> " .
            "Please enter URL string within <strong>URL</strong> as <br> " .
            "<strong>https:// < your domain > /order/confirm/flutterwave/{payment_id} </strong><br>" .
            "Set the random and secure secret hash." .
            "Next, click <strong>Receive Webhook response in JSON format</strong> and click <strong>Save</strong> button to submit request. " .
            "<strong>Note!</strong> Please make sure you have " .
            "<strong>Test mode</strong> option disabled in the main menu!"
        );

        $flutterwave['payment_options_info'] = _(
            "You can find that value in API documentation. Default set to 'card' "
        );

        $flutterwave['network_info'] = _(
            "You can find that value in API documentation. This is for mobile african payments (ex.'MTN')"
        );

        $flutterwave_test_checked = '';
        if ((null !== Input::post("input.flutterwave_test") &&
                (int)Input::post("input.flutterwave_test") === 1) ||
            (isset($data['flutterwave_test']) &&
                (int)$data['flutterwave_test'] === 1)
        ) {
            $flutterwave_test_checked = ' checked="checked"';
        }
        $flutterwave['test_checked'] = $flutterwave_test_checked;
        
        return $flutterwave;
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
        $data['flutterwave_public_key'] = $additional_values_validation->validated("input.flutterwave_public_key");
        $data['flutterwave_secret_key'] = $additional_values_validation->validated("input.flutterwave_secret_key");
        $data['flutterwave_secret_webhook_key'] = $additional_values_validation->validated("input.flutterwave_secret_webhook_key");
        $data['flutterwave_payment_options'] = $additional_values_validation->validated("input.flutterwave_payment_options");
        $data['flutterwave_network'] = $additional_values_validation->validated("input.flutterwave_network");
        $data['flutterwave_test'] = $additional_values_validation->validated("input.flutterwave_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
