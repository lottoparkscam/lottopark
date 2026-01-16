<?php

/** @deprecated - use new fixtures instead */
final class Test_Factory_Whitelabel_Raffle_Ticket_Line extends Test_Factory_Base
{
    protected function values(&$values): array
    {
        return [
            'status' => 0,
            'created_at' => time(),
            'updated_at' => time()
        ];
    }
}
