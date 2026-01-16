<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Models\MiniGameTransaction;

final class Add_Mini_Game_User_Promo_Code_Id_To_Mini_Game_Transaction_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        $table = MiniGameTransaction::table();
        $column = 'mini_game_user_promo_code_id';

        if (!DBUtil::field_exists($table, [$column])) {
            DBUtil::add_fields(
                $table,
                [
                    $column => [
                        'type' => 'bigint',
                        'constraint' => 10,
                        'unsigned' => true,
                        'default' => null,
                        'null' => true,
                        'after' => 'system_drawn_number'
                    ],
                ]
            );

            DBUtil::add_foreign_key(
                $table,
                Helper_Migration::generate_foreign_key(
                    $table,
                    $column
                )
            );
        }
    }

    protected function down_gracefully(): void
    {
        $table = MiniGameTransaction::table();
        $column = 'mini_game_user_promo_code_id';

        if (DBUtil::field_exists($table, [$column])) {
            DBUtil::drop_foreign_key(
                $table,
                Helper_Migration::generate_foreign_key(
                    $table,
                    $column
                )['constraint']
            );

            DBUtil::drop_fields($table, [$column]);
        }
    }
}