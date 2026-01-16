<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Lottery_And_Raffle_Id_Fields_To_Whitelabel_Bonus extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_bonus';

    protected function up_gracefully(): void
    {
        DBUtil::modify_fields($this->tableName,
            [
                'lottery_id' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'name' => 'purchase_lottery_id',
                ],
            ]
        );

        DBUtil::add_fields(
            $this->tableName,
            [
                'register_lottery_id' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'purchase_lottery_id'
                ],
                'purchase_raffle_id' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'register_lottery_id'
                ],
                'register_raffle_id' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'purchase_raffle_id'
                ],
            ]
        );

        DBUtil::add_foreign_key(
            $this->tableName,
            [
                'constraint' => 'whitelabel_bonus_register_lottery_id_foreign',
                'key' => 'register_lottery_id',
                'reference' => [
                    'table' => 'lottery',
                    'column' => 'id'
                ],
                'on_update' => 'RESTRICT',
                'on_delete' => 'CASCADE',
            ]
        );

        DBUtil::add_foreign_key(
            $this->tableName,
            [
                'constraint' => 'whitelabel_bonus_purchase_raffle_id_foreign',
                'key' => 'purchase_raffle_id',
                'reference' => [
                    'table' => 'raffle',
                    'column' => 'id'
                ],
                'on_update' => 'RESTRICT',
                'on_delete' => 'CASCADE',
            ]
        );

        DBUtil::add_foreign_key(
            $this->tableName,
            [
                'constraint' => 'whitelabel_bonus_register_raffle_id_foreign',
                'key' => 'register_raffle_id',
                'reference' => [
                    'table' => 'raffle',
                    'column' => 'id'
                ],
                'on_update' => 'RESTRICT',
                'on_delete' => 'CASCADE',
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            $this->tableName,
            [
                'register_lottery_id',
                'purchase_raffle_id',
                'register_raffle_id'
            ]
        );

        DBUtil::drop_foreign_key(
            $this->tableName,'whitelabel_bonus_register_lottery_id_foreign'
        );

        DBUtil::drop_foreign_key(
            $this->tableName,'whitelabel_bonus_purchase_raffle_id_foreign'
        );

        DBUtil::drop_foreign_key(
            $this->tableName,'whitelabel_bonus_register_raffle_id_foreign'
        );
    }
}
