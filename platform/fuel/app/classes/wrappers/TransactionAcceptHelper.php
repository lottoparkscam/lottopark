<?php


namespace Wrappers;

use Lotto_Helper;
use Model_Whitelabel_Transaction;

/**
 * @codeCoverageIgnore
 */
class TransactionAcceptHelper
{
    private Lotto_Helper $helper;

    public function __construct(Lotto_Helper $helper)
    {
        $this->helper = $helper;
    }

    public function accept(
        Model_Whitelabel_Transaction $transaction = null,
        string $out_id = null,
        array $data = null,
        array $whitelabel = null
    ): int {
        return $this->helper::accept_transaction($transaction, $out_id, $data, $whitelabel);
    }
}
