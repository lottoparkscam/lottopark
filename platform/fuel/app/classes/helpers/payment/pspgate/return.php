<?php

use Fuel\Core\Input;

final class Helpers_Payment_PspGate_Return extends Helpers_Payment_PspGate_Receiver implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    protected function fetch_input_fields(): array
    {
        return Input::post();
    }

    public function create_payment(): void
    {
        exit();
    }

    /**
     *
     * @param Model_Whitelabel_Transaction|null $transaction
     * @param string|null $out_id
     * @param array $data
     * @return bool
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = $this->receive_transaction(
            $transaction,
            $out_id,
            $data
        );

        return $ok;
    }

    /**
     * Allow all IPs because users get redirected back to us with result from payment page
     */
    protected function check_ip(): void
    {
        return;
    }
}
