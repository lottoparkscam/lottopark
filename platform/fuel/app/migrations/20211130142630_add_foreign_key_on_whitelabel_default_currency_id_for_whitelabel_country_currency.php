<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

/** Note that Helper_Migration::generate_foreign_key doesn't create key. It just returns the array */
final class Add_Foreign_Key_On_Whitelabel_Default_Currency_Id_For_Whitelabel_Country_Currency extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_country_currency';
    private string $index = 'whitelabel_default_currency_id';

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
