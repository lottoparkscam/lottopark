<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Unique_Key_To_Cloudflare_Zone extends Database_Migration_Graceful
{
    private string $tableName = 'cloudflare_zone';
    private array $index = ['whitelabel_id', 'identifier'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key($this->tableName, $this->index);
    }
}
