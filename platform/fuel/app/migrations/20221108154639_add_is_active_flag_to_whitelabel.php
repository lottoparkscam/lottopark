<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Models\Whitelabel;

final class Add_Is_Active_Flag_To_Whitelabel extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            Whitelabel::get_table_name(),
            [
                'is_active' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true, 'after' => 'name'],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            Whitelabel::get_table_name(),
            [
                'is_active',
            ]
        );
    }
}
