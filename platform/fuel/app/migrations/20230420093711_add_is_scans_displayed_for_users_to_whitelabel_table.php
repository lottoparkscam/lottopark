<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Models\Whitelabel;

final class Add_Is_Scans_Displayed_For_Users_To_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            Whitelabel::get_table_name(),
            [
                'is_scans_displayed_for_users' =>
                    Helper_Migration::FIELD_TYPE_BOOLEAN + [
                        'default' => true,
                        'after' => 'display_deposit_button'
                    ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            Whitelabel::get_table_name(),
            [
                'is_scans_displayed_for_users',
            ]
        );
    }
}
