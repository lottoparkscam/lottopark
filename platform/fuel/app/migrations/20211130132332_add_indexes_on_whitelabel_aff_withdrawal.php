<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_On_Whitelabel_Aff_Withdrawal extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_aff_withdrawal';
    private array $indexes = ['whitelabel_id', 'withdrawal_id'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexes);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexes);
    }
}
