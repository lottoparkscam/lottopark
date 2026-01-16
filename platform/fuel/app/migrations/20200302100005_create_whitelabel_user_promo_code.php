<?php

namespace Fuel\Migrations;

class Create_whitelabel_user_promo_code
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_user_promo_code',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_promo_code_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'whitelabel_transaction_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_promo_code_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel_promo_code',
                        'column' => 'id'
                    ]
                ],
                [
                    'key' => 'whitelabel_transaction_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel_transaction',
                        'column' => 'id'
                    ]
                ],
                [
                    'key' => 'whitelabel_user_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ]
                ]
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_user_promo_code');
    }
}
