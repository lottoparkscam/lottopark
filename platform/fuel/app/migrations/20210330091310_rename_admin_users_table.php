<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Rename_Admin_Users_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::rename_table('admin_users', 'admin_user');
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table('admin_user', 'admin_users');
    }
}