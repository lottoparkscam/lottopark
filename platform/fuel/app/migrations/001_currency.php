<?php

namespace Fuel\Migrations;

class Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'currency',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'code' => ['type' => 'varchar', 'constraint' => 3],
                'rate' => ['type' => 'decimal', 'constraint' => [10, 4], 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        \DBUtil::drop_table('currency');
    }
}
