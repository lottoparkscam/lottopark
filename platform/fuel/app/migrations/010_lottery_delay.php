<?php

namespace Fuel\Migrations;

class Lottery_Delay
{
    public function up()
    {
        \DBUtil::create_table(
            'lottery_delay',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date_local' => ['type' => 'date'],
                'date_delay' => ['type' => 'date']
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lottery_delay_lottery_id_lottery_idfx',
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

        // TODO: check if needed, or maybe date_local alone?
        \DBUtil::create_index('lottery_delay', 'lottery_id', 'lottery_delay_lottery_id_lottery_idfx_idx');
        \DBUtil::create_index('lottery_delay', ['lottery_id', 'date_local'], 'lottery_delay_lottery_id_date_local_idmx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_delay', 'lottery_delay_lottery_id_lottery_idfx');
        \DBUtil::drop_table('lottery_delay');
    }
}
