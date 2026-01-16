<?php

namespace Fuel\Migrations;

class Emerchant_User
{
    public function up()
    {
        \DBUtil::create_table(
            'emerchant_user',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'customer_id' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'emerchant_user_wu_id_wu_idfx',
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

        \DBUtil::create_index('emerchant_user', 'whitelabel_user_id', 'emerchant_user_wu_id_wu_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('emerchant_user', 'emerchant_user_wu_id_wu_idfx');

        \DBUtil::drop_table('emerchant_user');
    }
}
