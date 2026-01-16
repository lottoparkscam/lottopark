<?php

use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;

/**
 * Presenter for wordpress payment.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-05-22
 * Time: 11:21:25
 */
final class Presenter_Wordpress_Base_Box_Payment_Other extends Presenter_Wordpress_Presenter
{
    /**
     *
     * @var array
     */
    private $special_whitelabel_payment_methods = [];

    /**
     * Prepare data for underlying view.
     *
     * @return void
     */
    public function view()
    {
        $this->errors = Lotto_Settings::getInstance()->get("errors");

        $this->prepare_special_whitelabel_payment_methods();
        
        // prepare data for epg, only if it exists
        if (isset($this->special_whitelabel_payment_methods[Helpers_Payment_Method::EASY_PAYMENT_GATEWAY])) {
            // set closure for error classes, view will check using closure.
            $this->set_safe('has_error_epg', $this->closure_input_has_error_class('easy-payment-gateway'));
            // set closures for input values
            $closure_last_value_epg = $this->closure_input_last_value('user', 'easy-payment-gateway'); // null for array name - values for this form are not saved (so no need for fallback retrieval)
            $this->set_safe('last_value_epg', $closure_last_value_epg);
            $this->set_safe('selected_epg', $this->closure_selected($closure_last_value_epg));
        }

        // prepare data for astropay, only if it exists
        if (isset($this->special_whitelabel_payment_methods[Helpers_Payment_Method::ASTRO_PAY])) {
            // astropay closures
            $this->set_safe('has_error_ap', $this->closure_input_has_error_class('astro-pay'));
            $closure_last_value_ap = $this->closure_input_last_value('user', 'astro-pay'); // null for array name - values for this form are not saved (so no need for fallback retrieval)
            $this->set_safe('last_value_ap', $closure_last_value_ap);
            $this->set_safe('selected_ap', $this->closure_selected($closure_last_value_ap));
            // astropay variables
            $this->set('banks_ap', $this->fetch_astro_pay_banks());
        }

        // prepare data for pspgate, only if it exists
        if (isset($this->special_whitelabel_payment_methods[Helpers_Payment_Method::PSPGATE_ID])) {
            // set closure for error classes, view will check using closure.
            $this->set_safe('has_error_pspgate', $this->closure_input_has_error_class('pspgate'));
            // set closures for input values
            $closure_last_value_pspgate = $this->closure_input_last_value('user', 'pspgate'); // null for array name - values for this form are not saved (so no need for fallback retrieval)
            $this->set_safe('last_value_pspgate', $closure_last_value_pspgate);
            $this->set_safe('selected_pspgate', $this->closure_selected($closure_last_value_pspgate));
        }

        // prepare data for zen, only if it exists
        if (isset($this->special_whitelabel_payment_methods[Helpers_Payment_Method::ZEN_ID])) {
            // set closure for error classes, view will check using closure.
            $this->set_safe('has_error_zen', $this->closure_input_has_error_class('zen'));
            // set closures for input values
            $closure_last_value_zen = $this->closure_input_last_value('user', 'zen'); // null for array name - values for this form are not saved (so no need for fallback retrieval)
            $this->set_safe('last_value_zen', $closure_last_value_zen);
            $this->set_safe('selected_zen', $this->closure_selected($closure_last_value_zen));
        }
    }

    /**
     *
     * @return void
     */
    private function prepare_special_whitelabel_payment_methods(): void
    {
        foreach ($this->whitelabel_payment_methods as $key => $single_whitelabel_payment_method) {
            switch ($single_whitelabel_payment_method['payment_method_id']) {
                case Helpers_Payment_Method::EASY_PAYMENT_GATEWAY:
                    $this->special_whitelabel_payment_methods[Helpers_Payment_Method::EASY_PAYMENT_GATEWAY][] = $key;
                    break;
                case Helpers_Payment_Method::ASTRO_PAY:
                    $this->special_whitelabel_payment_methods[Helpers_Payment_Method::ASTRO_PAY][] = $key;
                    break;
                case Helpers_Payment_Method::PSPGATE_ID:
                    $this->special_whitelabel_payment_methods[Helpers_Payment_Method::PSPGATE_ID][] = $key;
                    break;
                case Helpers_Payment_Method::LENCO_ID:
                    $this->special_whitelabel_payment_methods[Helpers_Payment_Method::LENCO_ID][] = $key;
                    break;
                case Helpers_Payment_Method::ZEN_ID:
                    $this->special_whitelabel_payment_methods[Helpers_Payment_Method::ZEN_ID][] = $key;
                    break;
            }
        }
    }
    
    /**
     *
     * @param int $key
     * @return array|null
     */
    private function get_whitelabel_payment_method_by_key(int $key):? array
    {
        $whitelabel_payment_method = null;
        
        if (!empty($this->whitelabel_payment_methods[$key])) {
            $whitelabel_payment_method = $this->whitelabel_payment_methods[$key];
        }
        
        return $whitelabel_payment_method;
    }
    
    /**
     * Safely get banks from astropay gateway
     * NOTE: banks are fetched via curl.
     * @param string $country_code country code for which banks should be retrieved.
     * @return array
     */
    private function retrieve_banks(string $country_code): array
    {
        $astro_pay_method = null;
        $data = null;
        $response = null;
        $decoded_response = null;
        
        if (empty($this->special_whitelabel_payment_methods[Helpers_Payment_Method::ASTRO_PAY])) {
            return [];
        }
        
        //
        // NOTICE!!!! FIRST DATA OF ASTROPAY PAYMENT IS TAKEN HERE -
        // If there is one bank the data will be correct for all Astro methods.
        $whitelabel_payment_method_id = $this->special_whitelabel_payment_methods[Helpers_Payment_Method::ASTRO_PAY][0];
        
        try {
            // first we need to get data for astro payment
            
            $astro_pay_key = $whitelabel_payment_method_id;
            
            $astro_pay_method = $this->get_whitelabel_payment_method_by_key($astro_pay_key);
            
            $data = unserialize($astro_pay_method['data']);

            $streamline = new Helpers_Payment_Astropay_Streamline(
                $data['login'],
                $data['password'],
                $data['secret_key'],
                $data['is_test']
            );
            $response = $streamline->get_banks_by_country($country_code); // TODO: {Vordis 2019-05-28 15:40:08} I don't like doing everything through this object, but it isn't worth to rewrite it.
            // curl failure - no banks
            if ($response === false) {
                throw new \Exception("curl returned false.");
            }

            // decode json and check it's integrity - should have at least one item and code in it.
            $decoded_response = json_decode($response, true);
            if (!isset($decoded_response[0]) || !isset($decoded_response[0]['code'])) {
                throw new \Exception("invalid response");
            }

            // if we reached here we should have banks in decoded response
            // we need to pluck fields of interest, store in cache and return
            $banks = [];
            foreach ($decoded_response as $bank) {
                $banks[$bank['code']] = $bank['name'];
            }

            return $banks;
        } catch (\Throwable $throwable) {            
            return [];
        }
    }

    /**
     * Fetch banks for astro pay payment method.
     *
     * @return array banks (Key as bank_code, Value as bank name).
     */
    private function fetch_astro_pay_banks(): array
    {
        // fetch and cache list of banks for current user country.

        $country_code = Lotto_Helper::get_best_match_user_country();
        // check country_code if it's null return empty array - no banks for no country
        if ($country_code === null) {
            return [];
        }

        $cache_key = 'astro_pay_banks' . $country_code;

        try {
            return Cache::get($cache_key);
        } catch (CacheNotFoundException $exception) { // cache outdated or not existent - fetch new banks and store them in cache
            $banks = $this->retrieve_banks($country_code);
            Cache::set($cache_key, $banks, Helpers_Time::MINUTE_IN_SECONDS);
            return $banks;
        }
    }

    protected function closure_last_value_cached(Closure $input_last_value, array $userPaymentCache = []): Closure
    {
        return function (string $field) use ($input_last_value, $userPaymentCache): string {
            return !empty($userPaymentCache[$field]) ? $userPaymentCache[$field] : $input_last_value($field);
        };
    }
}
