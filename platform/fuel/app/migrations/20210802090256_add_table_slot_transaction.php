<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Table_Slot_Transaction extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::create_table(
            'slot_transaction',
            [
                'id' => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_slot_provider_id' => ['type' => 'bigint', 'unsigned' => true],
                'whitelabel_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'slot_game_id' => ['type' => 'bigint', 'unsigned' => true],
                'slot_open_game_id' => ['type' => 'bigint', 'unsigned' => true, 'null' => true],
                'currency_id' => ['type' => 'tinyint', 'unsigned' => true],
                'token' => ['type' => 'bigint', 'constraint' => 17, 'unsigned' => true],
                'is_canceled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => false, 'default' => false],
                'amount' => ['type' => 'decimal', 'constraint' => [7, 2]],
                'amount_usd' => ['type' => 'double', 'constraint' => [7, 2]],
                'amount_manager' => ['type' => 'double', 'constraint' => [7, 2]],
                'type' => ['constraint' => "'bet','tip','freespin','win','jackpot','refund','rollback'", 'type' => 'enum', 'null' => true],
                'action' => ['constraint' => "'bet','win','refund','rollback'", 'type' => 'enum'],
                'provider_transaction_id' => ['type' => 'varchar', 'constraint' => 255],
                'additional_data' => ['type' => 'json'],
                'created_at' => ['type' => 'datetime'],
                'canceled_at' => ['type' => 'datetime', 'null' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('slot_transaction', 'whitelabel_slot_provider_id'),
                Helper_Migration::generate_foreign_key('slot_transaction', 'whitelabel_user_id'),
                Helper_Migration::generate_foreign_key('slot_transaction', 'slot_game_id'),
                Helper_Migration::generate_foreign_key('slot_transaction', 'slot_open_game_id'),
                Helper_Migration::generate_foreign_key('slot_transaction', 'currency_id')
            ]
        );

        Helper_Migration::generate_unique_key('slot_transaction', ['whitelabel_slot_provider_id', 'token']);
        Helper_Migration::generate_unique_key('slot_transaction', ['whitelabel_slot_provider_id', 'provider_transaction_id']);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_table('slot_transaction');
    }
}
