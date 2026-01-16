<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_For_Whitelabel_User_Ticket extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user_ticket';
    private array $isInsuredIndex = ['is_insured'];
    private array $tierIndex = ['tier'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isInsuredIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->tierIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isInsuredIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->tierIndex);
    }
}
