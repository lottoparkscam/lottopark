<?php

namespace Fuel\Migrations;

class Whitelabel_Api_Ip
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_api_ip',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'ip' => ['type' => 'varchar', 'constraint' => 45],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_api_ip_w_id_w_idfx',
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

        \DBUtil::create_index('whitelabel_api_ip', 'ip', 'whitelabel_api_ip_idx');
        \DBUtil::create_index('whitelabel_api_ip', 'whitelabel_id', 'whitelabel_api_ip_w_id_w_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_api_ip', 'whitelabel_api_ip_w_id_w_idfx');

        \DBUtil::drop_table('whitelabel_api_ip');
    }
}
