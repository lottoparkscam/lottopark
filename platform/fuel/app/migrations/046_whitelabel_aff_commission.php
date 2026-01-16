<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Commission
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff_commission',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'payment_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 2],
                'type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'tier' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'commission' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'commission_usd' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'commission_payment' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'commission_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_commission_c_id_c_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'NO ACTION'
                ],
                [
                    'constraint' => 'whitelabel_aff_commission_wa_id_wa_idfx',
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_aff_commission_wt_id_wt_idfx',
                    'key' => 'whitelabel_transaction_id',
                    'reference' => [
                        'table' => 'whitelabel_transaction',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_aff_commission', 'whitelabel_aff_id', 'whitelabel_aff_commission_wa_id_wa_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_commission', 'whitelabel_transaction_id', 'whitelabel_aff_commission_wt_id_wt_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_commission', 'currency_id', 'whitelabel_aff_commission_c_id_c_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_commission', ['whitelabel_aff_id', 'whitelabel_transaction_id', 'is_accepted'], 'whitelabel_aff_commission_wa_id_wt_id_is_a_idmx');
        \DBUtil::create_index('whitelabel_aff_commission', 'type', 'whitelabel_aff_commission_type_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff_commission', 'whitelabel_aff_commission_c_id_c_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_commission', 'whitelabel_aff_commission_wa_id_wa_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_commission', 'whitelabel_aff_commission_wt_id_wt_idfx');

        \DBUtil::drop_table('whitelabel_aff_commission');
    }
}
