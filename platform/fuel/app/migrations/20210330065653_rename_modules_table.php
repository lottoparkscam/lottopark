<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Rename_Modules_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::rename_table('modules', 'module');
    }

    protected function down_gracefully(): void
    {
        DBUtil::rename_table('module', 'modules');

    }
}