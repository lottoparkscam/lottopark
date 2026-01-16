<?php

use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelUserTicketLine;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_User_Ticket_Line extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                                => 1,
            'whitelabel_user_ticket_id'         => 999,
            'lottery_type_data_id'              => 999,
            'whitelabel_user_ticket_slip_id'    => 999,
            'numbers'               => '[[1], [2], [3]]',
            'bnumbers'              => '[[1], [2], [3]]',
            'amount_local'          => 1000.00,
            'amount'                => 1000.00,
            'amount_usd'            => 1000.00,
            'amount_payment'        => 1000.00,
            'amount_manager'        => 1000.00,
            'status'                => 1,
            'payout'                => false,
            'prize_local'           => 1000.00,
            'prize_usd'             => 1000.00,
            'prize'                 => 1000.00,
            'prize_manager'         => 1000.00,
            'prize_net_local'       => 1000.00,
            'prize_net_usd'         => 1000.00,
            'prize_net'             => 1000.00,
            'prize_net_manager'     => 1000.00,
            'bonus_amount_local'    => 1000.00,
            'bonus_amount_payment'  => 1000.00,
            'bonus_amount_usd'      => 1000.00,
            'bonus_amount'          => 1000.00,
            'bonus_amount_manager'  => 1000.00,
            'uncovered_prize_local' => 1000.00,
            'uncovered_prize_usd'   => 1000.00,
            'uncovered_prize'       => 1000.00,
            'uncovered_prize_manager'   => 1000.00
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelUserTicketLine
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabel_user_ticket_line = new WhitelabelUserTicketLine($this->props);

        if ($save) {
            $whitelabel_user_ticket_line->save();
        }

        return $whitelabel_user_ticket_line;
    }
}
