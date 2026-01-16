<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_User extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user';
    private array $index = ['whitelabel_id', 'login', 'hash', 'is_deleted'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
