<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Payment_Method_Type_Status_Type_And_Date_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_transaction';
    private const COLUMNS = ['payment_method_type', 'type', 'status', 'date'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
