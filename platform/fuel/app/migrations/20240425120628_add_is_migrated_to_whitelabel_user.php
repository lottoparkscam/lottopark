<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Migrated_To_Whitelabel_User extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_user',
            [
                'is_migrated' => [
                    'type' => 'bool',
                    'null' => false,
                    'default' => false,
                    'after' => 'is_confirmed',
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_user',
            [
                'is_migrated',
            ]
        );
    }
}
