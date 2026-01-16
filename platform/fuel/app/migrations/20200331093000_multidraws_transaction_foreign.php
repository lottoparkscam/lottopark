<?php

namespace Fuel\Migrations;

class Multidraws_Transaction_Foreign
{
    public function up()
    {
        \DBUtil::add_fields('multi_draw', [
            'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'null' => true, 'after' => 'whitelabel_user_id', 'unsigned' => true],
        ]);
        
        \DBUtil::add_foreign_key('multi_draw', [
            'constraint' => 'multi_draw_wt_id_wt_id_idfx',
            'key' => 'whitelabel_transaction_id',
            'reference' => [
                'table' => 'whitelabel_transaction',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'SET NULL'
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('multi_draw', 'multi_draw_wt_id_wt_id_idfx');
    }
}
