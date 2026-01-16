<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Casino_Bonus_Balance_To_Whitelabel_User extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_user';
    private const COLUMN = 'casino_bonus_balance';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            self::TABLE,
            [
                self::COLUMN => [
                    'type' => 'decimal',
                    'constraint' => [9,2],
                    'unsigned' => true,
                    'default' => 0.00,
                    'after' => 'casino_balance'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            self::TABLE,
            [
                self::COLUMN,
            ]
        );
    }
}