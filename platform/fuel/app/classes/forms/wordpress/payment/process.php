<?php

/**
 *
 */
interface Forms_Wordpress_Payment_Process
{
    /**
     *
     * @return void
     */
    public function create_payment(): void;
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string $out_id
     * @param array $data
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool;
}
