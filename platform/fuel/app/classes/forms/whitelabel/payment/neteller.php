<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Neteller form
 */
final class Forms_Whitelabel_Payment_Neteller extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @param array $whitelabel
     * @return \Forms_Whitelabel_Payment_Neteller
     */
    public function set_whitelabel(array $whitelabel): Forms_Whitelabel_Payment_Neteller
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
        $validation = Validation::forge("neteller");
        
        $validation->add("input.appclientid", _("App Client ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 25);
        
        $validation->add("input.appclientsecret", _("App Client Secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 150);
        
        $validation->add("input.webhooksecretkey", _("Webhook Secret Key"))
            ->add_rule("trim")
            ->add_rule("max_length", 50);
        
        $validation->add("input.test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
                        
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
        $neteller = [];
        
        $appclientid_error_class = '';
        if (isset($errors['input.appclientid'])) {
            $appclientid_error_class = ' has-error';
        }
        $neteller['appclientid_error_class'] = $appclientid_error_class;
        
        $appclientid_value_t = '';
        if (null !== Input::post("input.appclientid")) {
            $appclientid_value_t = Input::post("input.appclientid");
        } elseif (isset($data['app_client_id'])) {
            $appclientid_value_t = $data['app_client_id'];
        }
        $neteller['appclientid_value'] = Security::htmlentities($appclientid_value_t);

        $appclientsecret_error_class = '';
        if (isset($errors['input.appclientsecret'])) {
            $appclientsecret_error_class = ' has-error';
        }
        $neteller['appclientsecret_error_class'] = $appclientsecret_error_class;
        
        $appclientsecret_value_t = '';
        if (null !== Input::post("input.appclientsecret")) {
            $appclientsecret_value_t = Input::post("input.appclientsecret");
        } elseif (isset($data['app_client_secret'])) {
            $appclientsecret_value_t = $data['app_client_secret'];
        }
        $neteller['appclientsecret_value'] = Security::htmlentities($appclientsecret_value_t);

        $neteller['appclientsecret_info'] = _(
            "Both Client ID &amp; Client Secret should be taken from " .
            "<strong>Neteller Merchant Dashboard &gt; Developer &gt; Apps</strong>."
        );

        $webhooksecretkey_error_class = '';
        if (isset($errors['input.webhooksecretkey'])) {
            $webhooksecretkey_error_class = ' has-error';
        }
        $neteller['webhooksecretkey_error_class'] = $webhooksecretkey_error_class;
        
        $webhooksecretkey_value_t = '';
        if (null !== Input::post("input.webhooksecretkey")) {
            $webhooksecretkey_value_t = Input::post("input.webhooksecretkey");
        } elseif (isset($data['webhook_secret_key'])) {
            $webhooksecretkey_value_t = $data['webhook_secret_key'];
        }
        $neteller['webhooksecretkey_value'] = Security::htmlentities($webhooksecretkey_value_t);

        $help_text_t = _(
            "Optional for non-shared accounts - improves payment confirmation speed. " .
            "Can be set in <strong>Neteller Merchant Dashboard &gt; " .
            "Developer &gt; Webhooks</strong>. You should set the " .
            "<strong>Webhook URL</strong> to <strong>%s</strong> and " .
            "check following hooks: <strong>payment_cancelled</strong>, " .
            "<strong>payment_declined</strong>, <strong>payment_failed" .
            "</strong>, <strong>payment_succeeded</strong>."
        );
        $domain_url = 'https://' . $this->whitelabel['domain'] .
            Helper_Route::ORDER_CONFIRM .
            Helpers_Payment_Method::NETELLER_URI .
            '/{payment_id}';
        $neteller['help_text'] = sprintf($help_text_t, $domain_url);

        $test_checked = '';
        if ((null !== Input::post("input.test") &&
                Input::post("input.test") == 1) ||
            (isset($data['test']) &&
                $data['test'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $neteller['test_checked'] = $test_checked;
        
        return $neteller;
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
        $data['app_client_id'] = $additional_values_validation->validated("input.appclientid");
        $data['app_client_secret'] = $additional_values_validation->validated("input.appclientsecret");
        $data['webhook_secret_key'] = $additional_values_validation->validated("input.webhooksecretkey");
        $data['test'] = $additional_values_validation->validated("input.test") == 1 ? 1 : 0;
        
        return $data;
    }
}
