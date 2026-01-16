<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Paysafecard form
 */
final class Forms_Whitelabel_Payment_Paysafecard extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @var string
     */
    private $platform_ip = "";
    
    /**
     *
     * @param string $platform_ip
     * @return \Forms_Whitelabel_Payment_Paysafecard
     */
    public function set_platform_ip(string $platform_ip): Forms_Whitelabel_Payment_Paysafecard
    {
        $this->platform_ip = $platform_ip;
        return $this;
    }

    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("paysafecard");
        
        $validation->add("input.apikey", _("API KEY"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 50);
        
        $validation->add("input.paysafecardtest", _("Test environment"))
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
        $paysafecard = [];
        
        $apikey_error_class = '';
        if (isset($errors['input.apikey'])) {
            $apikey_error_class = ' has-error';
        }
        $paysafecard['apikey_error_class'] = $apikey_error_class;
        
        $apikey_value_t = '';
        if (null !== Input::post("input.apikey")) {
            $apikey_value_t = Input::post("input.apikey");
        } elseif (isset($data['api_key'])) {
            $apikey_value_t = $data['api_key'];
        }
        $paysafecard['apikey_value'] = Security::htmlentities($apikey_value_t);

        $help_text_t = _(
            "Possible to create in the <strong>Merchant panel &gt; Test Data/Production " .
            "Data &gt; Generate new API key</strong>.<br>You will also need to add " .
            "server's IP to the whitelist in the <strong>Merchant panel &gt; IP " .
            "Whitelisting &gt; Type: <i>%s</i>, Subnet: <i>32</i> &gt; Request activation</strong>."
        );
        $paysafecard['help_text'] = sprintf($help_text_t, $this->platform_ip);

        $test_checked = '';
        if ((null !== Input::post("input.paysafecardtest") &&
                Input::post("input.paysafecardtest") == 1) ||
            (isset($data['test']) &&
                $data['test'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $paysafecard['test_checked'] = $test_checked;
        
        return $paysafecard;
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
        $data['api_key'] = $additional_values_validation->validated("input.apikey");
        $data['test'] = $additional_values_validation->validated("input.paysafecardtest") == 1 ? 1 : 0;
        
        return $data;
    }
}
