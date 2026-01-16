<?php

trait Services_Api_Customer
{
    /**
     * Check if customer-api is enabled and update data
     *
     * @param int $user_id
     * @param int $whitelabel_id
     * @param array $data
     * return void
     */
    public static function process_customer(int $user_id, int $whitelabel_id, array $data): void
    {
        $customer_api_plugin = Model_Whitelabel_Plugin::get_plugin_by_name($whitelabel_id, 'customer-api');
        if (isset($customer_api_plugin['is_enabled']) && $customer_api_plugin['is_enabled'] == true) {
            self::update_customer($customer_api_plugin, 'customers/', $user_id, $data);
        }
    }

    public static function update_customer($whitelabel_plugin, $path, $user_id, $data): bool
    {
        try {
            $customer_id = \Fuel::$env.'-'.$user_id;
            $customerio_url = $whitelabel_plugin['options']->url.$path;
            $site_id = $whitelabel_plugin['options']->site_id;
            $api_key = $whitelabel_plugin['options']->api_key;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $customerio_url.$customer_id);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_USERPWD, $site_id . ":" . $api_key);

            $result = curl_exec($curl);
            curl_close($curl);
            return $result !== false;
        } catch (Exception $ex) {
            Model_Whitelabel_Plugin_Log::add_log(
                Helpers_General::TYPE_ERROR,
                $whitelabel_plugin['id'] ?? null,
                "Problem while connecting Customer API: {$ex->getMessage()}"
            );
            return false;
        }
    }
}
