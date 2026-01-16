<?php

namespace Fuel\Migrations;

class Language
{
    public function up()
    {
        \DBUtil::create_table(
            'language',
            [
                'id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
                'default_currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'code' => ['type' => 'varchar', 'constraint' => 6],
                'js_currency_format' => ['type' => 'varchar', 'constraint' => 20],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'language_default_currency_id_currency_idfx',
                    'key' => 'default_currency_id',
                    'reference' => [
                        'table' => 'currency',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'SET NULL'
                ]
            ]
        );

        \DBUtil::create_index('language', 'code', 'language_code_idx');
        \DBUtil::create_index('language', 'default_currency_id', 'language_default_currency_id_currency_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('language', 'language_default_currency_id_currency_idfx');

        \DBUtil::drop_table('language');
    }
}
