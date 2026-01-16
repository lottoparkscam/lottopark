<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Slot_Game extends Database_Migration_Graceful
{
    private string $tableName = 'slot_game';
    private array $getGamesQueryIndex = ['is_deleted', 'is_mobile', 'has_lobby', 'slot_provider_id'];
    private array $isDeletedIndex = ['is_deleted'];
    private array $isMobileIndex = ['is_mobile'];
    private array $hasLobbyIndex = ['has_lobby'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->getGamesQueryIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isDeletedIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isMobileIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->hasLobbyIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->getGamesQueryIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isDeletedIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isMobileIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->hasLobbyIndex);
    }
}
