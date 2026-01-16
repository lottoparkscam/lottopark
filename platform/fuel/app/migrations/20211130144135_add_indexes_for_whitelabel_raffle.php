<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_Raffle extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_raffle';
    private array $indexes = ['is_enabled', 'is_margin_calculation_enabled', 'is_bonus_balance_in_use'];
    private array $isEnabledIndex = ['is_enabled'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexes);
        Helper_Migration::generateIndexKey($this->tableName, $this->isEnabledIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexes);
        Helper_Migration::dropIndexKey($this->tableName, $this->isEnabledIndex);
    }
}
