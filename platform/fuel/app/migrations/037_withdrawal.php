<?php

namespace Fuel\Migrations;

class Withdrawal
{
    public function up()
    {
        \DBUtil::create_table(
            'withdrawal',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'varchar', 'constraint' => 100]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        \DBUtil::drop_table('withdrawal');
    }
}
