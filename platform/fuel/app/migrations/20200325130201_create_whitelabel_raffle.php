<?php

namespace Fuel\Migrations;

class Create_whitelabel_raffle
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_raffle',
            [
                'id' => ['type' => 'int', 'auto_increment' => true, 'unsigned' => true],
                'whitelabel_id' => ['type' => 'int', 'unsigned' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint'=> 3, 'unsigned' => true],
                'income' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
                'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1]
            ],
            ['id'],
            true,
            false,
            null,
            [
                [
                    'constraint' => 'whitelabel_raffle_whitelabel_id_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ]
                ],
                [
                    'constraint' => 'whitelabel_raffle_raffle_id_idfx',
                    'key' => 'raffle_id',
                    'reference' => [
                        'table' => 'raffle',
                        'column' => 'id'
                    ]
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_raffle', 'whitelabel_id', 'whitelabel_raffle_whitelabel_id_idfx_idx');
        \DBUtil::create_index('whitelabel_raffle', 'raffle_id', 'whitelabel_raffle_raffle_id_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_whitelabel_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_raffle_id_idfx');

        \DBUtil::drop_table('whitelabel_raffle');
    }
}
