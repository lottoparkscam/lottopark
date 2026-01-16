<?php

namespace Fuel\Migrations;

class Whitelabel_Setting
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_setting',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'name' => ['type' => 'varchar', 'constraint' => 45],
                'value' => ['type' => 'text'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_setting_w_id_w_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_setting', 'whitelabel_id', 'whitelabel_setting_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_setting', ['whitelabel_id', 'name'], 'whitelabel_setting_w_id_name_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_setting', 'whitelabel_setting_w_id_w_idfx');

        \DBUtil::drop_table('whitelabel_setting');
    }
}
