<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Provider_In_Slot_Game extends Database_Migration_Graceful
{
    private const TABLE = 'slot_game';
    private const COLUMNS = ['provider'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
