<?php

namespace Fuel\Migrations;

class Whitelabel_Language
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_language',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'language_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'min_purchase_amount' => ['type' => 'decimal', 'constraint' => [7, 2], 'unsigned' => true, 'default' => 0.00],
                'min_deposit_amount' => ['type' => 'decimal', 'constraint' => [7, 2], 'unsigned' => true, 'default' => 0.00],
                'min_withdrawal' => ['type' => 'decimal', 'constraint' => [7, 2], 'unsigned' => true, 'default' => 10.00],
                'max_order_amount' => ['type' => 'decimal', 'constraint' => [7, 2], 'unsigned' => true, 'default' => 1000.00],
                'max_order_count' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 20],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_language_currency_id_currency_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ],
                [
                    'constraint' => 'whitelabel_language_language_id_language_idfx',
                    'key' => 'language_id',
                    'reference' => [
                        'table' => 'language',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_language_whitelabel_id_whitelabel_idfx',
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

        \DBUtil::create_index('whitelabel_language', 'whitelabel_id', 'whitelabel_language_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_language', 'language_id', 'whitelabel_language_language_id_language_idfx_idx');
        \DBUtil::create_index('whitelabel_language', 'currency_id', 'whitelabel_language_currency_id_currency_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_language', 'whitelabel_language_currency_id_currency_idfx');
        \DBUtil::drop_foreign_key('whitelabel_language', 'whitelabel_language_language_id_language_idfx');
        \DBUtil::drop_foreign_key('whitelabel_language', 'whitelabel_language_whitelabel_id_whitelabel_idfx');

        \DBUtil::drop_table('whitelabel_language');
    }
}
