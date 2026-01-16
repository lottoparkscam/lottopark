<?php

namespace Fuel\Migrations;

class Whitelabel_Blocked_Country
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_blocked_country',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'code' => ['type' => 'varchar', 'constraint' => 2]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_blocked_country_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_blocked_country', 'whitelabel_id', 'whitelabel_blocked_country_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_blocked_country', ['whitelabel_id', 'code'], 'whitelabel_id_code_unique', 'UNIQUE');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_blocked_country', 'whitelabel_blocked_country_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_blocked_country');
    }
}
