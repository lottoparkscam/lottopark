<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Date_Confirmed_Index_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_transaction';
    private array $index = ['date_confirmed', 'status'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
