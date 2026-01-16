<?php

namespace Fuel\Migrations;

class Create_whitelabel_user_group
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_group',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 100],
                'prize_payout_percent' => ['type' => 'decimal', 'constraint' => [5,2], 'null' => false, 'default' => 100.00]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_user_group');
    }
}

