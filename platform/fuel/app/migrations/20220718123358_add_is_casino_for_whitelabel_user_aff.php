<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelUserAff;

final class Add_Is_Casino_For_Whitelabel_User_Aff extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelUserAff::get_table_name(),
            [
                'is_casino' => [
                    'type' => 'boolean',
                    'default' => 0,
                    'after' => 'is_expired'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelUserAff::get_table_name(),
            [
                'is_casino',
            ]
        );
    }
}
