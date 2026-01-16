<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Update_Table_FlipCoin_Game_Transaction extends Database_Migration_Graceful
{
    private const TABLE = 'flipcoin_game_transaction';
    private const NEW_TABLE_NAME = 'mini_game_transaction';

    protected function up_gracefully(): void
    {
       DBUtil::rename_table(self::TABLE, self::NEW_TABLE_NAME);

       DBUtil::modify_fields(self::NEW_TABLE_NAME, [
           'flipcoin_game_id' => [
               'name' => 'mini_game_id',
               'type' => 'bigint',
               'unsigned' => true
           ],
       ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table(self::NEW_TABLE_NAME, self::TABLE);

        DBUtil::modify_fields(self::TABLE, [
            'mini_game_id' => [
                'name' => 'flipcoin_game_id',
                'type' => 'bigint',
                'unsigned' => true
            ],
        ]);
    }
}
