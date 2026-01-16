<?php

use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelUserTicketSlip;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_User_Ticket_Slip extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                        => 1,
            'whitelabel_user_ticket_id' => 1,
            'ticket_scan_url'           => 'some url',
            'additional_data'           => 'some additional data',
            'whitelabel_ltech_id'       => 1,
            'whitelabel_lottery_id'     => 999
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelUserTicketSlip
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabel_user_ticket_slip = new WhitelabelUserTicketSlip($this->props);

        if ($save) {
            $whitelabel_user_ticket_slip->save();
        }

        return $whitelabel_user_ticket_slip;
    }
}
