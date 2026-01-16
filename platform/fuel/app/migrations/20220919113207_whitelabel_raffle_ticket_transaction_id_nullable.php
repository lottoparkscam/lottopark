<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Whitelabel_Raffle_Ticket_Transaction_Id_Nullable extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_raffle_ticket',
            [
                'whitelabel_transaction_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_raffle_ticket',
            [
                'whitelabel_transaction_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true
                ],
            ]
        );
    }
}
