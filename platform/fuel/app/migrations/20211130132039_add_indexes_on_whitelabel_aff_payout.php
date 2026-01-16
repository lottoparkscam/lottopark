<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_On_Whitelabel_Aff_Payout extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_aff_payout';
    private array $whitelabelIdAndIsPaidOutIndex = ['whitelabel_id', 'is_paidout'];
    private array $isPaidOutIndex = ['is_paidout'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->whitelabelIdAndIsPaidOutIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isPaidOutIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->whitelabelIdAndIsPaidOutIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isPaidOutIndex);
    }
}
