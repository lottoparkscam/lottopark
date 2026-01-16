<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_To_Payment_Log extends Database_Migration_Graceful
{
    private string $tableName = 'payment_log';
    private array $index = ['type', 'whitelabel_payment_method_id', 'date'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
