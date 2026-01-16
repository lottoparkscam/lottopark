<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Force_Disable_To_Whitelabel_Slot_Provider_Subprovider extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_slot_provider_subprovider';
    private const COLUMN = 'force_disable';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            self::TABLE,
            [
                self::COLUMN => [
                    'type' => 'boolean',
                    'default' => false,
                    'after' => 'is_enabled'
                ],
            ]
        );

        DBUtil::create_index(self::TABLE, [self::COLUMN]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            self::TABLE,
            [
                self::COLUMN
            ]
        );
    }
}
