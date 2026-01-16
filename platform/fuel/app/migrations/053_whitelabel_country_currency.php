<?php

namespace Fuel\Migrations;

class Whitelabel_Country_Currency
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_country_currency',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'country_code' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
                'whitelabel_default_currency_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 1]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_country_currency_w_id_w_idfx_idx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_country_currency_wdc_id_c_idfx_idx',
                    'key' => 'whitelabel_default_currency_id',
                    'reference' => [
                        'table' => 'whitelabel_default_currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('whitelabel_country_currency', 'whitelabel_id', 'whitelabel_country_currency_w_id_w_idfx_idx');
        \DBUtil::create_index('whitelabel_country_currency', 'whitelabel_default_currency_id', 'whitelabel_country_currency_wdc_id_c_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_country_currency', 'whitelabel_country_currency_w_id_w_idfx_idx');
        \DBUtil::drop_foreign_key('whitelabel_country_currency', 'whitelabel_country_currency_wdc_id_c_idfx_idx');

        \DBUtil::drop_table('whitelabel_country_currency');
    }
}
