<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Create_whitelabel_user_tickets_keno_data
{
    public function up()
    {
        DBUtil::create_table('whitelabel_user_ticket_keno_data', [
            'id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
                'auto_increment' => true
            ],
            'whitelabel_user_ticket_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
            ],
            'lottery_type_multiplier_id' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'null' => false,
            ],
            'numbers_per_line' => [
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
                    'key' => 'lottery_type_multiplier_id',
                    'reference' => [
                        'table' => 'lottery_types_multipliers',
                        'column' => 'id',
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE',
                ],
                [
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id',
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE',
                ]
            ]
        );
    }

    public function down()
    {
        DBUtil::drop_table('whitelabel_user_tickets_keno_data');
    }
}