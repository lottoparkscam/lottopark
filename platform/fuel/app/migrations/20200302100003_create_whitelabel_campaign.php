<?php

namespace Fuel\Migrations;

class Create_whitelabel_campaign
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_campaign',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'bonus_type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'max_codes_user' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'max_users_per_code' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'prefix' => ['type' => 'varchar', 'constraint' => 100],
                'is_active' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
                'date_start' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'date_end' => ['type' => 'datetime', 'null' => true, 'default' => null],
                'max_users' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'discount_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'discount_type' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ],
                [
                    'key' => 'whitelabel_aff_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ]
                ],
                [
                    'key' => 'lottery_id',
                    'reference' =>
                    [
                        'table' => 'lottery',
                        'column' => 'id'
                    ]
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_campaign');
    }
}
