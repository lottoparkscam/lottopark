<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Type_Status_And_Date_To_Whitelabel_Transactions extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_transaction';
    private const COLUMNS = ['type', 'status', 'date'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
