<?php

use Fuel\Core\Validation;
use Helpers\UrlHelper;

/**
 * Class for preparing Piastrix form
 */
final class Forms_Whitelabel_Payment_Piastrix extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
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
     * @return \Forms_Whitelabel_Payment_Piastrix
     */
    public function set_whitelabel(array $whitelabel = null): Forms_Whitelabel_Payment_Piastrix
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
        $validation = Validation::forge("piastrix");
        
        $validation->add("input.shopid", _("Shop ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);
        
        $validation->add("input.secretkey", _("Secret Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 100);
        
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
        $piastrix = [];
        
        $shopid_error_class = '';
        if (isset($errors['input.shopid'])) {
            $shopid_error_class = ' has-error';
        }
        $piastrix['shopid_error_class'] = $shopid_error_class;
        
        $shopid_value_t = '';
        if (null !== Input::post("input.shopid")) {
            $shopid_value_t = Input::post("input.shopid");
        } elseif (isset($data['shop_id'])) {
            $shopid_value_t = $data['shop_id'];
        }
        $piastrix['shop_id_value'] = Security::htmlentities($shopid_value_t);

        $piastrix['show_id_info'] = _(
            "Can be found in the <strong>Merchant panel &gt; API &gt; " .
            "Shop &gt; Shops &gt; ID (next to your shop name)</strong>."
        );

        $secretkey_error_class = '';
        if (isset($errors['input.secretkey'])) {
            $secretkey_error_class = ' has-error';
        }
        $piastrix['secret_key_error_class'] = $secretkey_error_class;
        
        $secretkey_value_t = '';
        if (null !== Input::post("input.secretkey")) {
            $secretkey_value_t = Input::post("input.secretkey");
        } elseif (isset($data['secret_key'])) {
            $secretkey_value_t = $data['secret_key'];
        }
        $piastrix['secret_key_value'] = Security::htmlentities($secretkey_value_t);

        $piastrix['secret_key_info'] = _(
            "Can be found in the <strong>Merchant panel &gt; API &gt; Shop " .
            "&gt; Shops &gt; Settings icon &gt; Security &gt; Security " .
            "key (Generate new private key)</strong>."
        );

        $help_text_t = _(
            "You should configure the shop settings in <strong>Merchant panel &gt; API " .
            "&gt; Shop &gt; Shops &gt; Settings icon &gt; Technical</strong> as " .
            "follows:<br><strong>Shop URLs:</strong> %s<br><strong>Notification " .
            "URLs:</strong> %s<br><strong>Success URLs:</strong> %s<br><strong>Failed " .
            "URLs:</strong> %s<br>Also, please <strong>do not</strong> check " .
            "<strong>Check uniqueness of payments</strong>."
        );

        $domain_url = UrlHelper::changeAbsoluteUrlToCasinoUrl('https://' . $this->whitelabel['domain']);
        
        $piastrix_url = $domain_url .
            Helper_Route::ORDER_CONFIRM .
            Helpers_Payment_Method::PIASTRIX_URI .
            '/{payment_id}';
        
        $success_url = $domain_url . Helper_Route::ORDER_SUCCESS;
        $failure_url = $domain_url . Helper_Route::ORDER_FAILURE;

        $piastrix['help_text'] = sprintf(
            $help_text_t,
            $domain_url,
            $piastrix_url,
            $success_url,
            $failure_url
        );
        
        return $piastrix;
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
        $data['shop_id'] = $additional_values_validation->validated("input.shopid");
        $data['secret_key'] = $additional_values_validation->validated("input.secretkey");
        
        return $data;
    }
}
