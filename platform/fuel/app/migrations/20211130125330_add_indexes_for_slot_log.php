<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Slot_Log extends Database_Migration_Graceful
{
    private string $tableName = 'slot_log';
    private array $isErrorIndex = ['is_error'];
    private array $actionIndex = ['action'];
    private array $isErrorAndActionIndex = ['is_error', 'action'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isErrorIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->actionIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isErrorAndActionIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isErrorIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->actionIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isErrorAndActionIndex);
    }
}
