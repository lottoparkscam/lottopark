<?php

namespace Fuel\Migrations;

class Whitelabel_Aff_Payout
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_aff_payout',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_aff_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'date' => ['type' => 'date'],
                'amount' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [9, 2], 'unsigned' => true],
                'amount_manager' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.00],
                'commissions' => ['type' => 'mediumint', 'constraint' => 8, 'unsigned' => true],
                'is_paidout' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_aff_payout_c_id_c_idfx',
                    'key' => 'currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_aff_payout_w_id_w_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_aff_payout_wa_id_wa_idfx',
                    'key' => 'whitelabel_aff_id',
                    'reference' => [
                        'table' => 'whitelabel_aff',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_aff_payout', 'whitelabel_id', 'whitelabel_aff_payout_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_payout', 'whitelabel_aff_id', 'whitelabel_aff_payout_wa_id_wa_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_payout', 'currency_id', 'whitelabel_aff_payout_c_id_c_idfx_idx');
        \DBUtil::create_index('whitelabel_aff_payout', ['whitelabel_id', 'whitelabel_aff_id', 'date'], 'whitelabel_aff_payout_w_id_wa_id_date_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_aff_payout', 'whitelabel_aff_payout_c_id_c_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_payout', 'whitelabel_aff_payout_w_id_w_idfx');
        \DBUtil::drop_foreign_key('whitelabel_aff_payout', 'whitelabel_aff_payout_wa_id_wa_idfx');

        \DBUtil::drop_table('whitelabel_aff_payout');
    }
}
