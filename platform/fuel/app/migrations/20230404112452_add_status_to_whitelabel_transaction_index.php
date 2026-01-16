<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Status_To_Whitelabel_Transaction_Index extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_transaction';
    private const COLUMNS = ['type', 'is_casino', 'status'];

    protected function up_gracefully(): void
    {
        DBUtil::drop_index(self::TABLE, 'type_is_casino_index');
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
        Helper_Migration::generateIndexKey(self::TABLE, ['type', 'is_casino']);
    }
}
