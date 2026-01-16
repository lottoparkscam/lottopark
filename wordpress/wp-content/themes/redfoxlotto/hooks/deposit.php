<?php
// get parent class.
require_once 'hook.php'; // NOTE: it will load only once (no reloading after first load) and will raise ERROR if file is not found.

/**
 * Deposit hook.
 */
class Deposit_Hook_Redfox extends Hook_Redfox // TODO: it may be a good idea to export this class, so it won't redefine itself on include.
{// 31.01.2019 12:02 Vordis TODO: maybe instead of suffix use proper namespaces

    /**
     * Send deposit data.
     * @return bool true if message was properly sent.
     */
    public function send_deposit_data()
    {
        // get instance of lotto settings
        $lotto_settings = Lotto_Settings::getInstance();
        // get parameters
        $whitelabel = $lotto_settings->get('transaction_whitelabel');
        $transaction = $lotto_settings->get("transaction_hook");
        $client_ip = $lotto_settings->get('client_ip_hook');

        // build short uri (dynamic part of the address) from parameters
        $uri_short = '';
        $uri_short.= '&idev_saleamt=' . ($transaction->amount - $transaction->payment_cost);
        $uri_short.= '&idev_order_num=' . $whitelabel['prefix'] . "D" . $transaction->token;
        $uri_short.= '&ip_address=' . $client_ip;

        return parent::send_data($uri_short);
    }
}

// static $deposit; // TODO: it may be a good idea to cache deposit, but again it would require once, better yet export from this file.
// send data
(new Deposit_Hook_Redfox())->send_deposit_data();
