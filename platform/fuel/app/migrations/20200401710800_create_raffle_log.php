<?php

namespace Fuel\Migrations;

class Create_raffle_log
{
    public function up()
    {
        \DBUtil::create_table(
            'raffle_log',
            [
                'id' => ['type' => 'int', 'constraint' => 20, 'auto_increment' => true, 'unsigned' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint'=> 3, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'type' => ['type' => 'tinyint', 'contraint' => 3, 'unsigned' => true],
                'message' => ['type' => 'varchar', 'constraint'=> 255]
            ],
            ['id'],
            true,
            false,
            null,
            [
                [
                    'constraint' => 'raffle_log_raffle_id_idfx',
                    'key' => 'raffle_id',
                    'reference' => [
                        'table' => 'raffle',
                        'column' => 'id'
                    ]
                ]
            ]
        );
        
        \DBUtil::create_index('raffle_log', 'raffle_id', 'raffle_log_raffle_id_idfx_idx');
    }
    
    public function down()
    {
        \DBUtil::drop_foreign_key('raffle_log', 'raffle_log_raffle_id_idfx');
        \DBUtil::drop_table('raffle_log');
    }
}
