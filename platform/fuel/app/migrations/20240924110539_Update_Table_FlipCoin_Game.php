<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Update_Table_FlipCoin_Game extends Database_Migration_Graceful
{
    private const TABLE = 'flipcoin_game';
    private const NEW_TABLE_NAME = 'mini_game';

    protected function up_gracefully(): void
    {
        DBUtil::rename_table(self::TABLE, self::NEW_TABLE_NAME);

        DBUtil::add_fields(
            self::NEW_TABLE_NAME,
            [
                'default_bet' => [
                    'type' => 'decimal',
                    'constraint' => [5, 2],
                    'after' => 'available_bets',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table(self::NEW_TABLE_NAME, self::TABLE);

        DBUtil::drop_fields(
            self::TABLE,
            [
                'default_bet',
            ]
        );
    }
}
