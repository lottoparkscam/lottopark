<?php

/**
 * Archetype of hooks.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
abstract class Hook_Redfox // 31.01.2019 12:02 Vordis TODO: maybe instead of suffix use proper namespaces
{

    /**
     * Base uri, where hooks should send messages.
     */
    const BASE_URI = 'https://redfoxaffiliates.com/idevaffiliate/sale.php?profile=72198';

    use \Traits_Sends_Post;

    /**
     * Check if current environment is production.
     * @return bool true if production.
     */
    private function is_production_environment(): bool
    {
        return \Fuel::$env === \Fuel::PRODUCTION;
    }

    /**
     * Send data 
     * @param string $uri_short last part of the address where data should be sent.
     * @return bool true if data was successfully sent.
     */
    protected function send_data(string $uri_short): bool
    {
        // send only for production evironment
        if (!$this->is_production_environment()) {
            return false;
        }

        // ok - send data
        return $this->send_message(self::BASE_URI . $uri_short);
    }
}
