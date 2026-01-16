<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_transaction_last_attempt_date extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'payment_attempt_date' => [
                    'type' => 'datetime',
                    'null' => true,
                    'after' => 'status'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'payment_attempt_date',
            ]
        );
    }
}
