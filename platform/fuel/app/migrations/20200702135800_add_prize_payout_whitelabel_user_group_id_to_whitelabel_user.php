<?php

namespace Fuel\Migrations;

class Add_prize_payout_whitelabel_user_group_id_to_whitelabel_user
{
    public function up()
    {
        \DBUtil::add_fields(
            'whitelabel_user',
            ['prize_payout_whitelabel_user_group_id' => [
                'type' => 'int', 
                'constraint' => 10, 
                'unsigned' => true, 
                'null' => true,
                'default' => null
            ]]
        );

        \DBUtil::add_foreign_key('whitelabel_user', [
            'constraint' => 'whitelabel_user_whitelabel_user_group_id_idfx',
            'key' => 'prize_payout_whitelabel_user_group_id',
            'reference' => [
                'table' => 'whitelabel_user_group',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'SET NULL'
        ]);
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_whitelabel_user_group_id_idfx');

        \DBUtil::drop_fields(
            'whitelabel_user',
            ['prize_payout_whitelabel_user_group_id']
        );
    }
}
