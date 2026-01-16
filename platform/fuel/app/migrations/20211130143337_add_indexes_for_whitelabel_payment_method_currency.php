<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_Payment_Method_Currency extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_payment_method_currency';
    private array $isEnabledIndex = ['is_enabled'];
    private array $isDefaultIndex = ['is_default'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isEnabledIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isDefaultIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isEnabledIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isDefaultIndex);
    }
}
