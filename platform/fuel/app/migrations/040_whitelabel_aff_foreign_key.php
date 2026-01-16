<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Foreign_Key
{
    public function up()
    {
        \DBUtil::add_foreign_key('whitelabel_aff', [
            'constraint' => 'whitelabel_aff_wap_id_wap_idpx',
            'key' => 'whitelabel_aff_parent_id',
            'reference' => [
                'table' => 'whitelabel_aff',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'SET NULL'
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff', 'whitelabel_aff_wap_id_wap_idpx');
    }
}
