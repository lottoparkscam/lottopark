<?php

use Fuel\Core\Validation;

/**
 * Class for preparing PayPal form
 */
final class Forms_Whitelabel_Payment_Paypal extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("paypal");
        
        $validation->add("input.logo_url_paypal", _("Logo url"))
            ->add_rule("trim")
            ->add_rule("valid_url")
            ->add_rule("max_length", 1024);

        $validation->add("input.paypaltest", _("Test environment"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.api_client_id_paypal", _("API Client ID"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("input.api_client_secret_paypal", _("API Client Secret"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

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
        $paypal = [];
        
        $logourl_error_class = '';
        if (isset($errors['input.logo_url_paypal'])) {
            $logourl_error_class = ' has-error';
        }
        $paypal['logourl_error_class'] = $logourl_error_class;
        
        $logourl_value_t = '';
        if (null !== Input::post("input.logo_url_paypal")) {
            $logourl_value_t = Input::post("input.logo_url_paypal");
        } elseif (isset($data['logo_url_paypal'])) {
            $logourl_value_t = $data['logo_url_paypal'];
        }
        $paypal['logourl_value'] = Security::htmlentities($logourl_value_t);

        $apiclientid_error_class = '';
        if (isset($errors['input.api_client_id_paypal'])) {
            $apiclientid_error_class = ' has-error';
        }
        $paypal['apiclientid_error_class'] = $apiclientid_error_class;
        
        $apiclientid_value_t = '';
        if (null !== Input::post("input.api_client_id_paypal")) {
            $apiclientid_value_t = Input::post("input.api_client_id_paypal");
        } elseif (isset($data['api_client_id_paypal'])) {
            $apiclientid_value_t = $data['api_client_id_paypal'];
        }
        $paypal['apiclientid_value'] = Security::htmlentities($apiclientid_value_t);

        $apiclientsecret_error_class = '';
        if (isset($errors['input.api_client_secret_paypal'])) {
            $apiclientsecret_error_class = ' has-error';
        }
        $paypal['apiclientsecret_error_class'] = $apiclientsecret_error_class;
        
        $apiclientsecret_value_t = '';
        if (null !== Input::post("input.api_client_secret_paypal")) {
            $apiclientsecret_value_t = Input::post("input.api_client_secret_paypal");
        } elseif (isset($data['api_client_secret_paypal'])) {
            $apiclientsecret_value_t = $data['api_client_secret_paypal'];
        }
        $paypal['apiclientsecret_value'] = Security::htmlentities($apiclientsecret_value_t);

        $test_checked = '';
        if ((null !== Input::post("input.paypaltest") &&
                Input::post("input.paypaltest") == 1) ||
            (isset($data['paypaltest']) &&
                $data['paypaltest'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $paypal['test_checked'] = $test_checked;
        
        $paypal['text_info_url'] = _(
            "The URL of the 150x50-pixel image displayed as your logo in the upper left " .
            "corner of the PayPal checkout pages."
        );

        $paypal['text_info_1'] = _(
            "To get your <b>API Credentials</b> follow these steps. Go to " .
            "<b>https://developer.paypal.com/</b> and login to the dashboard using " .
            "your seller account credentials. Go to <b>My Apps & Credentials</b> tab, " .
            "scroll down to <b>REST API apps</b> header. Click <b>Create App</b>. " .
            "Name your App e.g. <b>Whitelotto Payments</b> and select your " .
            "sandbox <b>*-facilitator</b> account. You should see <b>SANDBOX API " .
            "CREDENTIALS</b> on the next page, to get your credentials for live " .
            "site click <b>Live</b> button in the top right corner. Copy " .
            "<b>Client ID</b> string to our <b>API Client ID</b> field, then copy " .
            "<b>Client Secret</b> string to our <b>API Client Secret</b> field."
        );

        $paypal['text_info_2'] = _("If you use SandBox remember to change email to 'SandBox email'");

        $paypal['text_info_3'] = _(
            "Make sure that the <strong>EUR</strong> currency is added to the list of " .
            "currencies supported by PayPal to accept payments correctly."
        );

        $paypal['text_info_4'] = _(
            "Log in to your PayPal account, then from the main menu " .
            "(<strong>Summary</strong>) select <strong>Currencies</strong>, " .
            "then on the following page, Add <strong>EUR</strong> Currency."
        );

        $paypal['text_info_5'] = _(
            "You have to change language encoding to UTF-8. Go to Language Encoding " .
            "section of your Paypal Profile " .
            "(https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-language-encoding) " .
            "Click the <strong>More Options</strong> button. Select <strong>UTF-8</strong> " .
            "encode and to the question <strong>Do you want to use the same encoding " .
            "for data sent from PayPal to you (e.g., IPN, downloadable " .
            "logs, emails)?</strong>, answer <strong>Yes</strong>. Click Save"
        );

        return $paypal;
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
        $data['api_client_id_paypal'] = $additional_values_validation->validated("input.api_client_id_paypal");
        $data['api_client_secret_paypal'] = $additional_values_validation->validated("input.api_client_secret_paypal");
        $data['logo_url_paypal'] = $additional_values_validation->validated("input.logo_url_paypal");
        $data['paypaltest'] = $additional_values_validation->validated("input.paypaltest") == 1 ? 1 : 0;
        
        return $data;
    }
}
