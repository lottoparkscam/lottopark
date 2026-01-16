<?php

namespace Fuel\Migrations;

class Add_referrer_id_to_whitelabel_user
{
    public function up()
    {
        //Create referrer_id in whitelabel_user table
        \DBUtil::add_fields('whitelabel_user', [
            'referrer_id' => ['type' => 'integer', 'constraint' => 10, 'null' => true, 'unsigned' => true]
        ]);
        
        \DBUtil::add_foreign_key('whitelabel_user', [
            'constraint' => 'whitelabel_user_referrer_id_idfx',
            'key' => 'referrer_id',
            'reference' => [
                'table' => 'whitelabel_user',
                'column' => 'id'
            ]
        ]);
        
        if (\DBUtil::field_exists('whitelabel_user', ['refer_by'])) {
            \DBUtil::drop_fields('whitelabel_user', ['refer_by']);
        }
        
        //Create whitelabel_user_id in whitelabel_refer_statistics
        \DBUtil::modify_fields('whitelabel_refer_statistics', [
            'refer' => ['name' => 'token', 'type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
        ]);
        \DBUtil::add_fields('whitelabel_refer_statistics', [
            'whitelabel_user_id' => ['type' => 'integer', 'constraint' => 10, 'unsigned' => true, 'after' => 'whitelabel_id']
         ]);
        \DBUtil::add_foreign_key('whitelabel_refer_statistics', [
            'constraint' => 'whitelabel_refer_statistics_whitelabel_user_id_idfx',
            'key' => 'whitelabel_user_id',
            'reference' => [
                'table' => 'whitelabel_user',
                'column' => 'id'
            ]
        ]);
        
        if (\DBUtil::field_exists('whitelabel_refer_statistics', ['refer'])) {
            \DBUtil::drop_fields('whitelabel_refer_statistics', ['refer']);
        }
        
        //Create indexes
        \DBUtil::create_index('whitelabel_user', 'referrer_id', 'whitelabel_user_referrer_id_idfx');
        \DBUtil::create_index('whitelabel_refer_statistics', 'whitelabel_user_id', 'whitelabel_refer_statistics_whitelabel_user_id_idfx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_user', 'whitelabel_user_referrer_id_idfx');
        \DBUtil::drop_foreign_key('whitelabel_refer_statistics', 'whitelabel_refer_statistics_whitelabel_user_id_idfx');
        
        \DBUtil::drop_fields('whitelabel_user', [
            'referrer_id'
        ]);
        \DBUtil::drop_fields('whitelabel_refer_statistics', [
            'whitelabel_user_id'
        ]);
        
        \DBUtil::add_fields('whitelabel_user', [
            'refer_by' => ['type' => 'integer', 'constraint' => 10, 'null' => true, 'unsigned' => true]
        ]);
        \DBUtil::modify_fields('whitelabel_refer_statistics', [
            'token' => ['name' => 'refer', 'type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
        ]);
    }
}
