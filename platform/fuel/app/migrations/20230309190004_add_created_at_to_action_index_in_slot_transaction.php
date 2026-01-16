<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Created_At_To_Action_Index_In_Slot_Transaction extends Database_Migration_Graceful
{
    private const TABLE = 'slot_transaction';
    private const COLUMNS = ['action', 'created_at'];
    private const ACTION_INDEX = ['action'];

    protected function up_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::ACTION_INDEX);
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
        Helper_Migration::generateIndexKey(self::TABLE, self::ACTION_INDEX);
    }
}
