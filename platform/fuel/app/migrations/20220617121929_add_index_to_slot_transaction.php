<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Index_To_Slot_Transaction extends Database_Migration_Graceful
{
    private string $tableName = 'slot_transaction';
    private array $index = ['is_canceled', 'created_at', 'action', 'amount_usd', 'whitelabel_slot_provider_id'];
    private array $indexToCountProviders = ['slot_game_id', 'whitelabel_slot_provider_id', 'action'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
        Helper_Migration::generateIndexKey($this->tableName, $this->indexToCountProviders);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
        Helper_Migration::dropIndexKey($this->tableName, $this->indexToCountProviders);
    }
}
