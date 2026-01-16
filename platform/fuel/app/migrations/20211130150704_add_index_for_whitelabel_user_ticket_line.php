<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_For_Whitelabel_User_Ticket_Line extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user_ticket_line';
    private array $index = ['status'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
