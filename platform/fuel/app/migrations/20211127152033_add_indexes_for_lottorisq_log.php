<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Lottorisq_Log extends Database_Migration_Graceful
{
    private string $tableName = 'lottorisq_log';
    private array $indexColumns = [
        'type',
        'whitelabel_user_ticket_id',
        'date'
    ];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->indexColumns);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->indexColumns);
    }
}
