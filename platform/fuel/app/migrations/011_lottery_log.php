<?php

namespace Fuel\Migrations;

class Lottery_Log
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_log',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'message' => ['type' => 'text', 'null' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_log_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('lottery_log', 'date', 'lottery_log_date_idx');
        \DBUtil::create_index('lottery_log', 'type', 'lottery_log_type_idx');
        \DBUtil::create_index('lottery_log', 'lottery_id', 'lottery_log_lottery_id_lottery_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_log', 'lottery_log_lottery_id_lottery_idfx');
        \DBUtil::drop_table('lottery_log');
    }
}
