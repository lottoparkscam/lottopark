<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Type_And_Is_Casino_To_Whitelabel_Transactions extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_transaction';
    private const COLUMNS = ['type', 'is_casino'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
