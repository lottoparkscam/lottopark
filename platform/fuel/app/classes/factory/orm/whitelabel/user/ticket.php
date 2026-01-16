<?php

use Models\WhitelabelUserTicket;
use Classes\Orm\AbstractOrmModel;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_User_Ticket extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                        => 1,
            'token'                     => 12345,
            'whitelabel_user_id'        => 999,
            'whitelabel_id'             => 999,
            'lottery_type_id'           => 127,
            'lottery_id'                => 255,
            'currency_id'               => 255,
            'multi_draw_id'             => 999,
            'whitelabel_transaction_id' => 999,
            'lottery_provider_id'       => 999,
            'valid_to_draw'             => '2019-02-10',
            'draw_date'                 => '2020-02-10',
            'amount_local'              => 1000.00,
            'amount'                    => 1000.00,
            'amount_usd'                => 1000.00,
            'amount_payment'            => 1000.00,
            'amount_manager'            => 1000.00,
            'date'                      => '2019-02-10',
            'date_processed'            => '2019-02-10',
            'status'                    => 0,
            'is_synchronized'           => false,
            'paid'                      => false,
            'payout'                    => false,
            'prize_payout_percent'      => 66.66666666,
            'prize_local'               => 1000.00,
            'prize'                     => 1000.00,
            'prize_usd'                 => 1000.00,
            'prize_manager'             => 1000.00,
            'prize_net_local'           => 1000.00,
            'prize_net'                 => 1000.00,
            'prize_net_usd'             => 1000.00,
            'prize_net_manager'         => 1000.00,
            'prize_jackpot'             => 1,
            'prize_quickpick'           => 1,
            'model'                     => 1,
            'cost_local'                => 1000.00,
            'cost'                      => 1000.00,
            'cost_usd'                  => 1000.00,
            'cost_manager'              => 1000.00,
            'income_local'              => 1000.00,
            'income'                    => 1000.00,
            'income_usd'                => 1000.00,
            'income_manager'            => 1000.00,
            'income_value'              => 100.00,
            'income_type'               => 1,
            'is_insured'                => false,
            'tier'                      => 1,
            'margin_local'              => 100.00,
            'margin'                    => 100.00,
            'margin_usd'                => 100.00,
            'margin_manager'            => 100.00,
            'margin_value'              => 100.00,
            'bonus_amount_local'        => 1000.00,
            'bonus_amount_payment'      => 1000.00,
            'bonus_amount_usd'          => 1000.00,
            'bonus_amount'              => 1000.00,
            'bonus_amount_manager'      => 1000.00,
            'bonus_cost_local'          => 1000.00,
            'bonus_cost'                => 1000.00,
            'bonus_cost_usd'            => 1000.00,
            'bonus_cost_manager'        => 1000.00,
            'has_ticket_scan'           => false,
            'ip'                        => '127.0.0.1',
            'line_count'                => 5
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelUserTicket
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $ticket = new WhitelabelUserTicket($this->props);

        if ($save) {
            $ticket->save();
        }

        return $ticket;
    }
}
