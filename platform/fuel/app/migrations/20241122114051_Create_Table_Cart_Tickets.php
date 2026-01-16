<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Create_Table_Cart_Tickets extends Database_Migration_Graceful
{
    private const TABLE = 'cart_tickets';

    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            self::TABLE,
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'cart_id' => ['type' => 'bigint', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 10, 'unsigned' => true],
                'ticket_multiplier' => ['type' => 'int', 'null' => true],
                'numbers_per_line' => ['type' => 'int', 'null' => true],
                'multidraw' => ['type' => 'json', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key(self::TABLE, 'cart_id', destinationTable: 'carts'),
                Helper_Migration::generate_foreign_key(self::TABLE, 'lottery_id', destinationTable: 'lottery')
            ]
        );

    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table(self::TABLE);
    }
}