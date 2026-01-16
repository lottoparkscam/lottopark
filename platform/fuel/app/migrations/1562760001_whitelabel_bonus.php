<?php

namespace Fuel\Migrations;

/**
 * Description of Whitelabel_Bonus
 *
 */
class Whitelabel_Bonus
{
    public function up()
    {
        \DBUtil::create_table(
            'whitelabel_bonus',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'bonus_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'whitelabel_bonus_whitelabel_id_whitelabel_idfx',
                    'key' => 'whitelabel_id',
                    'reference' => [
                        'table' => 'whitelabel',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'whitelabel_bonus_bonus_id_bonus_idfx',
                    'key' => 'bonus_id',
                    'reference' => [
                        'table' => 'bonus',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('whitelabel_bonus', 'whitelabel_id', 'whitelabel_bonus_whitelabel_id_whitelabel_idfx');
        \DBUtil::create_index('whitelabel_bonus', 'bonus_id', 'whitelabel_bonus_bonus_id_bonus_idfx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('whitelabel_bonus', 'whitelabel_bonus_whitelabel_id_whitelabel_idfx');
        \DBUtil::drop_foreign_key('whitelabel_bonus', 'whitelabel_bonus_bonus_id_bonus_idfx');

        \DBUtil::drop_table('whitelabel_bonus');
    }
}
