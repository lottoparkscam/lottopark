<?php

namespace Fuel\Migrations;

class Whitelabel_Ltech_Table
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_ltech',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'key' => ['type' => 'varchar', 'constraint' => 255]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_ltech_w_id_w_idfx_idx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_ltech', 'whitelabel_id', 'whitelabel_ltech_w_id_w_idfx_idx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_ltech', 'whitelabel_ltech_w_id_w_idfx_idx');

        \DBUtil::drop_table('whitelabel_ltech');
    }
}
