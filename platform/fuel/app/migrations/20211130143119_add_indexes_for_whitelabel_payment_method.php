<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_Payment_Method extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_payment_method';
    private array $showIndex = ['show'];
    private array $onlyDepositIndex = ['only_deposit'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->showIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->onlyDepositIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->showIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->onlyDepositIndex);
    }
}
