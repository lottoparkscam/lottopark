<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\MiniGameTransaction;

final class add_is_bonus_balance_paid_to_mini_game_transaction_table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        $table = MiniGameTransaction::get_table_name();
        $column = 'is_bonus_balance_paid';

        if (!DBUtil::field_exists($table, [$column])) {
            DBUtil::add_fields(
                $table,
                [
                    $column => [
                        'type' => 'tinyint',
                        'constraint' => 1,
                        'default' => false,
                        'after' => 'mini_game_user_promo_code_id',
                    ],
                ]
            );
        }
    }

    protected function down_gracefully(): void
    {
        $table = MiniGameTransaction::get_table_name();
        $column = 'is_bonus_balance_paid';

        if (DBUtil::field_exists($table, [$column])) {
            DBUtil::drop_fields($table, [$column]);
        }
    }
}
