<?php

namespace Fuel\Migrations;

class Whitelabel_Prepaid
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_prepaid',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'date' => ['type' => 'datetime'],
                'amount' => ['type' => 'decimal', 'constraint' => [10, 2]],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_prepaid_w_id_w_idfx',
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

        \DBUtil::create_index('whitelabel_prepaid', 'whitelabel_id', 'whitelabel_prepaid_w_id_w_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_prepaid', 'whitelabel_prepaid_w_id_w_idfx');

        \DBUtil::drop_table('whitelabel_prepaid');
    }
}
