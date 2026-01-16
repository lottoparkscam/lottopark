<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;
use Fuel\Core\DBUtil;

final class Add_Foreign_Key_For_Whitelabel_Prepaid extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_prepaid';
    private string $index = 'whitelabel_transaction_id';

    protected function up_gracefully(): void
    {
        $foreignKey = Helper_Migration::generate_foreign_key($this->tableName, $this->index);
        DBUtil::add_foreign_key($this->tableName, $foreignKey);
    }

    protected function down_gracefully(): void
    {
        $foreignKey = Helper_Migration::generate_foreign_key($this->tableName, $this->index);
        DBUtil::drop_foreign_key($this->tableName, $foreignKey['constraint']);
    }
}
