<?php

namespace Fuel\Migrations;

class Add_default_whitelabel_user_group_id_to_whitelabel
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel',
            ['default_whitelabel_user_group_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null]]
        );

        \DBUtil::add_foreign_key('whitelabel', [
            'constraint' => 'whitelabel_default_whitelabel_user_group_id_idfx',
            'key' => 'default_whitelabel_user_group_id',
            'reference' => [
                'table' => 'whitelabel_user_group',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'SET NULL'
        ]);

        \DBUtil::create_index('whitelabel', 'default_whitelabel_user_group_id', 'whitelabel_default_whitelabel_user_group_id_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel', 'whitelabel_default_whitelabel_user_group_id_idfx');
        
        \DBUtil::drop_fields(
            'whitelabel',
            ['default_whitelabel_user_group_id']
        );
    }
}
