<?php

use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelTransaction;
use Models\WhitelabelRaffleTicket;
use Fuel\Tasks\Factory\Utils\Faker;
/**
 * @deprecated - use new fixtures instead
 *
 * Class Factory_Orm_Transaction
 * @Author Sebastian Twaróg <sebastian.twarog@gg.international>
 *
 * @UnitTest missing
 * @FeatureTest missing
 * @E2ETest not required
 *
 * @method static WhitelabelTransaction[] create(int $amount, array $props = [], ?Closure $closure = null)
 * @method static WhitelabelTransaction[] make(int $amount, array $props = [], ?Closure $closure = null)
 */
class Factory_Orm_Transaction extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $defaults = [
            'additional_data' => null,
            'amount' => 10.0,
            'amount_manager' => null,
            'amount_payment' => null,
            'amount_usd' => null,
            'cost' => null,
            'cost_manager' => null,
            'cost_usd' => null,
            'currency_id' => 1,
            'date' => Faker::forge()->dateTime()->format('Y-m-d H:i:s'),
            'date_confirmed' => Faker::forge()->dateTime()->format('Y-m-d H:i:s'),
            'income' => null,
            'income_manager' => null,
            'income_usd' => null,
            'margin' => null,
            'margin_manager' => null,
            'margin_usd' => null,
            'payment_cost' => null,
            'payment_cost_manager' => null,
            'payment_cost_usd' => null,
            'payment_currency_id' => 1,
            'payment_method_type' => null,
            'payment_attempt_date' => null,
            'status' => 1,
            'token' => Faker::forge()->randomNumber(),
            'transaction_out_id' => null,
            'type' => 1,
            'whitelabel_cc_method_id' => null,
            'whitelabel_payment_method_id' => 1000,
            'whitelabel_id' => self::HARDCODED_WHITELABEL_ID,
            'whitelabel_user_id' => self::HARDCODED_WHITELABEL_USER_ID,
        ];
        $this->props = array_merge($defaults, $props);
    }

    /** @deprecated - use new fixtures instead */
    public static function for_ticket(WhitelabelRaffleTicket $ticket): self
    {
        return new static([
            'amount' => $ticket->amount,
            'currency_id' => $ticket->currency_id,
            'payment_currency_id' => $ticket->currency_id,
            'date' => $ticket->draw_date->format('mysql'),
            'whitelabel_user_id' => $ticket->whitelabel_user_id,
        ]);
    }

    /**
     * @deprecated - use new fixtures instead
     * @param bool $save
     *
     * @return WhitelabelTransaction
     * @throws ErrorException
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $transaction = new WhitelabelTransaction($this->props);
        $common_currency = Factory_Orm_Currency::forge()->build(false);

        if (empty($this->props['currency_id'])) {
            $transaction->currency = $common_currency;
        }
        if (empty($this->props['payment_currency_id'])) {
            $transaction->payment_currency = $common_currency;
        }

        if ($save) {
            $transaction->save();
        }
        return $transaction;
    }
}
