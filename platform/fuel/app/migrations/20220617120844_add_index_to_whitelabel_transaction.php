<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_To_Whitelabel_Transaction extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_transaction';
    private array $index = ['status', 'date'];
    private array $indexPaymentMethods = ['whitelabel_cc_method_id', 'payment_method_type', 'status', 'type', 'whitelabel_id', 'date'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
        Helper_Migration::generateIndexKey($this->tableName, $this->indexPaymentMethods);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
        Helper_Migration::dropIndexKey($this->tableName, $this->indexPaymentMethods);
    }
}
