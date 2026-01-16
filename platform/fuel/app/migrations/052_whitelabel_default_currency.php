<?php

namespace Fuel\Migrations;

class Whitelabel_Default_Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_default_currency',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2],
                'is_default_for_site' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
                'default_deposit_first_box' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 20.00],
                'default_deposit_second_box' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 50.00],
                'default_deposit_third_box' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 100.00],
                'min_purchase_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 1.00],
                'min_deposit_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 4.00],
                'min_withdrawal' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 10.00],
                'max_order_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 1000.00],
                'max_deposit_amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'unsigned' => true, 'default' => 1000.00]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_default_currency_c_id_c_idfx_idx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_default_currency_w_id_w_idfx_idx',
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

        \DBUtil::create_index('whitelabel_default_currency', 'whitelabel_id', 'whitelabel_default_currency_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_default_currency', 'currency_id', 'whitelabel_default_currency_c_id_c_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_default_currency', 'whitelabel_default_currency_c_id_c_idfx_idx');
        \DBUtil::drop_foreign_key('whitelabel_default_currency', 'whitelabel_default_currency_w_id_w_idfx_idx');

        \DBUtil::drop_table('whitelabel_default_currency');
    }
}
