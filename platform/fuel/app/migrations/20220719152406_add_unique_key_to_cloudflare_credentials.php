<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Unique_Key_To_Cloudflare_Credentials extends Database_Migration_Graceful
{
    private string $tableName = 'cloudflare_credentials';
    private array $index = ['auth_email'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key($this->tableName, $this->index);
    }
}
