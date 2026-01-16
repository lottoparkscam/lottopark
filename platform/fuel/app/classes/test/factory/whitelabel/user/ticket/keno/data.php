<?php


use Fuel\Core\Arr;

/** @deprecated - use new fixtures instead */
class Test_Factory_Whitelabel_User_Ticket_Keno_Data extends Test_Factory_Base
{
    /**
     * @inheritDoc
     */
    protected function values(array &$values): array
    {
        $values = Arr::subset($values, [
            'whitelabel_user_ticket_id',
            'lottery_type_multiplier_id',
            'numbers_per_line',
        ]);

        return $values;
    }
}
