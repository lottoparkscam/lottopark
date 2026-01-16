<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_transaction_attempts_count extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'payment_attempts_count' => [
                    'type' => 'smallint',
                    'default' => 0,
                    'after' => 'payment_attempt_date'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'payment_attempts_count',
            ]
        );
    }
}
