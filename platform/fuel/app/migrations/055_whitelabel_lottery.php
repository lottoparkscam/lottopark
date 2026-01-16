<?php

namespace Fuel\Migrations;

class Whitelabel_Lottery
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_lottery',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_provider_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true],
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'model' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'income' => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true, 'default' => 1.00],
                'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
                'tier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
                'volume' => ['type' => 'decimal', 'constraint' => [10, 0], 'unsigned' => true, 'default' => 1000],
                'min_lines' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 1]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_lottery_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_lottery_provider_id_lottery_provider_idfx',
                    'key' => 'lottery_provider_id',
                    'reference' => [
                        'table' => 'lottery_provider',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'whitelabel_lottery_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_lottery', 'whitelabel_id', 'whitelabel_lottery_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_lottery', 'lottery_id', 'whitelabel_lottery_lottery_id_lottery_idfx_idx');
        \DBUtil::create_index('whitelabel_lottery', 'is_enabled', 'whitelabel_lottery_is_enabled_idx');
        \DBUtil::create_index('whitelabel_lottery', ['whitelabel_id', 'is_enabled'], 'whitelabel_lottery_whitelabel_id_is_enabled_idmx');
        \DBUtil::create_index('whitelabel_lottery', ['lottery_id', 'is_enabled'], 'whitelabel_lottery_lottery_id_is_enabled_idmx');
        \DBUtil::create_index('whitelabel_lottery', ['whitelabel_id', 'lottery_id'], 'whitelabel_lottery_w_id_l_id_idmx');
        \DBUtil::create_index('whitelabel_lottery', 'lottery_provider_id', 'whitelabel_lottery_provider_id_lottery_provider_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_lottery', 'whitelabel_lottery_lottery_id_lottery_idfx');
        \DBUtil::drop_foreign_key('whitelabel_lottery', 'whitelabel_lottery_provider_id_lottery_provider_idfx');
        \DBUtil::drop_foreign_key('whitelabel_lottery', 'whitelabel_lottery_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_lottery');
    }
}
