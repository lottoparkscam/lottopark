<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Create_lottery_type_numbers_per_line
{
    public function up()
    {
        DBUtil::create_table('lottery_type_numbers_per_line', [
            'id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
            'lottery_type_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
            ],
            'min' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
                'default' => 1
            ],
            'max' => [
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
        DBUtil::drop_table('lottery_type_numbers_per_line');
    }
}