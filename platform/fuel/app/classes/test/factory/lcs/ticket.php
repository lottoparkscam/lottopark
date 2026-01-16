<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Lcs Ticket.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-09-20
 * Time: 14:53:41
 */
final class Test_Factory_Lcs_Ticket extends Test_Factory_Base
{
    protected function values(array &$values): array
    {
        return [
            'uuid' => function (): string {
                return uniqid();
            },
        ];
    }
}
