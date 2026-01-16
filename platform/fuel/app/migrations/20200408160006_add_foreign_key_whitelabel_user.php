<?php

namespace Fuel\Migrations;

class Add_foreign_key_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_foreign_key('whitelabel_user', [
            'constraint' => 'whitelabel_user_connected_aff_idfx',
            'key' => 'connected_aff_id',
            'reference' => [
                'table' => 'whitelabel_aff',
                'column' => 'id'
            ]
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_connected_aff_idfx');
    }
}
