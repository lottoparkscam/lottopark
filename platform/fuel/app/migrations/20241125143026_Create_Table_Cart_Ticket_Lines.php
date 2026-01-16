<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Cart_Ticket_Lines extends Database_Migration_Graceful
{
    private const TABLE = 'cart_ticket_lines';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'cart_ticket_id' => ['type' => 'bigint', 'constraint' => 10, 'unsigned' => true],
                'numbers' => ['type' => 'json'],
                'bnumbers' => ['type' => 'json']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'cart_ticket_id', destinationTable: 'cart_tickets'),
            ]
        );

    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}