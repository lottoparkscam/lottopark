<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelPluginUser;

final class Add_Created_At_And_Updated_At_To_Whitelabel_Plugin_User extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelPluginUser::get_table_name(),
            [
                'created_at' => ['type' => 'datetime', 'null' => true],
                'updated_at' => ['type' => 'datetime', 'null' => true],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelPluginUser::get_table_name(),
            [
                'created_at', 'updated_at'
            ]
        );
    }
}
