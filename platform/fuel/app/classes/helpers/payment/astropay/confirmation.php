<?php

/**
 * Astropay confirmation receiver.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-05-24
 * Time: 12:08:40
 */
final class Helpers_Payment_Astropay_Confirmation extends Helpers_Payment_Astropay_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;
    
    // this may be needed in some payment methods - when return differ from confirmation.
    
    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        exit();
    }
    
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
    ): bool {
        $ok = $this->receive_transaction($transaction, $out_id, $data);
        
        return $ok;
    }
}
