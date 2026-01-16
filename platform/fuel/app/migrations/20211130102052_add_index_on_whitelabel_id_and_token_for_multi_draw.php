<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Whitelabel_Id_And_Token_For_Multi_Draw extends Database_Migration_Graceful
{
    private string $tableName = 'multi_draw';
    private array $indexes = ['whitelabel_id', 'token'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexes);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexes);
    }
}
