<?php

namespace Fuel\Migrations;

class Whitelabel_Api
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_api',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'api_key' => ['type' => 'varchar', 'constraint' => 32],
                'api_secret' => ['type' => 'varchar', 'constraint' => 64],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_api_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_api', 'api_key', 'whitelabel_api_api_key_idx');
        \DBUtil::create_index('whitelabel_api', 'whitelabel_id', 'whitelabel_api_whitelabel_id_whitelabel_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_api', 'whitelabel_api_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_api');
    }
}
