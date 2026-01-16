<?php

namespace Fuel\Migrations;

class Create_whitelabel_promo_code
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_promo_code',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'token' => ['type' => 'varchar', 'constraint' => 20, 'null' => true, 'default' => null],
                'whitelabel_campaign_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'key' => 'whitelabel_campaign_id',
                    'reference' =>
                    [
                        'table' => 'whitelabel_campaign',
                        'column' => 'id'
                    ]
                ],
            ]
        );
    }

    public function down()
    {
        \DBUtil::drop_table('whitelabel_promo_code');
    }
}
