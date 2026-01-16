<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Limit_Enabled_To_Whitelabel_Slot_Provider extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_slot_provider',
            [
                'is_limit_enabled' => [
                    'type' => 'tinyint',
                    'default' => 0,
                    'after' => 'is_enabled'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_slot_provider',
            [
                'is_limit_enabled',
            ]
        );
    }
}