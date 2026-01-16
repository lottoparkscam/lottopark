<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Created_At_In_Slow_Log_Table extends Database_Migration_Graceful
{
    private const TABLE = 'slot_log';
    private const COLUMNS = ['created_at'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
