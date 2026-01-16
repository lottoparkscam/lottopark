<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Is_Enabled_For_Payment_Method extends Database_Migration_Graceful
{
    private string $tableName = 'payment_method';
    private array $index = ['is_enabled_for_casino'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
