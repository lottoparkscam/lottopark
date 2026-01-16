<?php

namespace Fuel\Migrations;

class Whitelabel_Withdrawal
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_withdrawal',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'withdrawal_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_withdrawal_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_withdrawal_withdrawal_id_withdrawal_idfx',
                    'key' => 'withdrawal_id',
                    'reference' => [
                        'table' => 'withdrawal',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_withdrawal', 'whitelabel_id', 'whitelabel_withdrawal_whitelabel_id_whitelabel_idfx_idx');
        \DBUtil::create_index('whitelabel_withdrawal', 'withdrawal_id', 'whitelabel_withdrawal_withdrawal_id_withdrawal_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_withdrawal', 'whitelabel_withdrawal_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('whitelabel_withdrawal', 'whitelabel_withdrawal_withdrawal_id_withdrawal_idfx');

        \DBUtil::drop_table('whitelabel_withdrawal');
    }
}
