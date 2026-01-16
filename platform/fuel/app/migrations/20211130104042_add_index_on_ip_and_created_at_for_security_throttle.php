<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Ip_And_Created_At_For_Security_Throttle extends Database_Migration_Graceful
{
    private string $tableName = 'security_throttle';
    private array $indexes = ['ip', 'created_at'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexes);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexes);
    }
}
