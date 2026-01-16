<?php

namespace Fuel\Migrations;

class Payment_Method
{
    public function up()
    {
        \DBUtil::create_table(
            'payment_method',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'name' => ['type' => 'varchar', 'constraint' => 100],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        \DBUtil::drop_table('payment_method');
    }
}
