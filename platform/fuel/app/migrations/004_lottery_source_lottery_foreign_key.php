<?php

namespace Fuel\Migrations;

class Lottery_Source_Lottery_Foreign_Key
{
    public function up()
    {
        \DBUtil::add_foreign_key('lottery_source', [
            'constraint' => 'lottery_source_lotter_id_lottery_idfx',
            'key' => 'lottery_id',
            'reference' => [
                'table' => 'lottery',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'CASCADE'
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('lottery_source', 'lottery_source_lotter_id_lottery_idfx');
    }
}
