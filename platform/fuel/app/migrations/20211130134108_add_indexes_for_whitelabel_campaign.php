<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_Campaign extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_campaign';
    private array $isActiveIndex = ['is_active'];
    private array $typeIndex = ['type'];
    private array $isActiveAndTypeIndex = ['is_active', 'type'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isActiveIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->typeIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isActiveAndTypeIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isActiveIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->typeIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isActiveAndTypeIndex);
    }
}
