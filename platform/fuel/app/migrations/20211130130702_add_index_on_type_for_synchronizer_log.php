<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Type_For_Synchronizer_Log extends Database_Migration_Graceful
{
    private string $tableName = 'synchronizer_log';
    private array $index = ['type'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
