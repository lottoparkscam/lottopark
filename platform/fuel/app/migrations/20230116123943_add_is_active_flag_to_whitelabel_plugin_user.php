<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Models\WhitelabelPluginUser;

final class Add_Is_Active_Flag_To_Whitelabel_Plugin_User extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelPluginUser::get_table_name(),
            [
                'is_active' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true, 'after' => 'whitelabel_plugin_id'],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelPluginUser::get_table_name(),
            [
                'is_active',
            ]
        );
    }
}
