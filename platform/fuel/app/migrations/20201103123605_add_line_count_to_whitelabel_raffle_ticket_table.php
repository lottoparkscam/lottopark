<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

class Add_Line_Count_To_Whitelabel_Raffle_Ticket_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_raffle_ticket',
            [
                'line_count' => [
                    'type' => 'int',
                    'constraint' => 3,
                    'unsigned' => true,
                    'default' => 0,
                    'after' => 'updated_at'
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_raffle_ticket',
            [
                'line_count'
            ]
        );
    }
}