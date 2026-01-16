<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Nonce_For_Whitelabel_Api_Nonce extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_api_nonce';
    private array $index = ['nonce'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
