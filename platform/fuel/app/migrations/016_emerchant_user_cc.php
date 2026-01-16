<?php

namespace Fuel\Migrations;

class Emerchant_User_Cc
{
    public function up()
    {
        \DBUtil::create_table(
            'emerchant_user_cc',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'order_id' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true],
                'type' => ['type' => 'varchar', 'constraint' => 255],
                'card_number' => ['type' => 'varchar', 'constraint' => 20],
                'exp_month' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'exp_year' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_lastused' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'emerchant_user_cc_wu_id_wu_idfx',
                    'key' => 'whitelabel_user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('emerchant_user_cc', 'whitelabel_user_id', 'emerchant_user_cc_wu_id_wu_idfx_idx');
        \DBUtil::create_index('emerchant_user_cc', ['whitelabel_user_id', 'type', 'card_number', 'exp_month', 'exp_year'], 'euc_wu_id_type_cn_em_ey_idmx');
        \DBUtil::create_index('emerchant_user_cc', ['whitelabel_user_id', 'is_deleted'], 'euc_wu_id_is_deleted_idmx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('emerchant_user_cc', 'emerchant_user_cc_wu_id_wu_idfx');

        \DBUtil::drop_table('emerchant_user_cc');
    }
}
