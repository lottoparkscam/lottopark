<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Indexes_For_Payment_Log extends Database_Migration_Graceful
{
    private string $tableName = 'payment_log';
    private array $indexColumns = [
        'type',
        'payment_method_id',
        'date'
    ];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexColumns);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexColumns);
    }
}
