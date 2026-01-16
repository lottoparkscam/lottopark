<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Create_lottery_types_multipliers_table
{
    public function up()
    {
        DBUtil::create_table('lottery_types_multipliers', [
            'id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
            'lottery_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
            ],
            'lottery_type_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
            ],
            'multiplier' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
            ],
        ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                ],
                [
                    'key' => 'lottery_type_id',
                    'reference' => [
                        'table' => 'lottery_type',
                        'column' => 'id'
                    ],
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('lottery_types_multipliers');
    }
}