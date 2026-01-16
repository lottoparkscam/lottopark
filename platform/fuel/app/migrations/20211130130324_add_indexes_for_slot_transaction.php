<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Slot_Transaction extends Database_Migration_Graceful
{
    private string $tableName = 'slot_transaction';
    private array $isCanceledIndex = ['is_canceled'];
    private array $typeIndex = ['type'];
    private array $actionIndex = ['action'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isCanceledIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->typeIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->actionIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isCanceledIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->typeIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->actionIndex);
    }
}
