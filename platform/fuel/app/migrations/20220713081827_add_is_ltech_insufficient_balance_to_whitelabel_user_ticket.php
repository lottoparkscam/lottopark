<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Ltech_Insufficient_Balance_To_Whitelabel_User_Ticket extends Database_Migration_Graceful
{
    private const TABLE_NAME = 'whitelabel_user_ticket';
    private const COLUMN_NAME = 'is_ltech_insufficient_balance';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            self::TABLE_NAME,
            [
                self::COLUMN_NAME => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'default' => false,
                    'after' => 'is_insured'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            self::TABLE_NAME,
            [
                self::COLUMN_NAME,
            ]
        );
    }
}