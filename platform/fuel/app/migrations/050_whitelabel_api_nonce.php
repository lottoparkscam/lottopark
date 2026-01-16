<?php

namespace Fuel\Migrations;

class Whitelabel_Api_Nonce
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_api_nonce',
            [
                'id' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'nonce' => ['type' => 'varchar', 'constraint' => 16],
                'date' => ['type' => 'datetime'],
                'data' => ['type' => 'text'],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_api_nonce_w_id_w_idfx',
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

        \DBUtil::create_index('whitelabel_api_nonce', 'whitelabel_id', 'whitelabel_api_nonce_w_id_w_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_api_nonce', 'whitelabel_api_nonce_w_id_w_idfx');

        \DBUtil::drop_table('whitelabel_api_nonce');
    }
}
