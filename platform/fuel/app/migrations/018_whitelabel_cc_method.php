<?php

namespace Fuel\Migrations;

class Whitelabel_Cc_Method
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_cc_method',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'method' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'settings' => ['type' => 'text'],
                'cost_percent' => ['type' => 'decimal', 'constraint' => [4, 2], 'unsigned' => true, 'default' => 0.00],
                'cost_fixed' => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true, 'default' => 0.00],
                'cost_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'payment_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'wcm_c_c_id_c_idfx',
                    'key' => 'cost_currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'wcm_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_cc_method', 'whitelabel_id', 'wcm_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_cc_method', ['whitelabel_id', 'method'], 'wcm_w_id_method_idmx');
        \DBUtil::create_index('whitelabel_cc_method', 'cost_currency_id', 'wcm_c_c_id_c_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_cc_method', 'wcm_c_c_id_c_idfx');
        \DBUtil::drop_foreign_key('whitelabel_cc_method', 'wcm_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_cc_method');
    }
}
