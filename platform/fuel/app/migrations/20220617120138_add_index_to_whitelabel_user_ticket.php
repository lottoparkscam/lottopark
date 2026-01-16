<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_To_Whitelabel_User_Ticket extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user_ticket';
    private array $index = ['whitelabel_id', 'paid', 'lottery_id', 'date'];
    private array $indexCountPaid = ['paid', 'date'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
        Helper_Migration::generateIndexKey($this->tableName, $this->indexCountPaid);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
        Helper_Migration::dropIndexKey($this->tableName, $this->indexCountPaid);
    }
}
