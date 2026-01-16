<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Update_Slot_Open_Game_Unique_Key extends Database_Migration_Graceful
{
    private const TABLE_NAME = 'slot_open_game';

    protected function up_gracefully(): void
    {
        Helper_Migration::updateIndex(
            self::TABLE_NAME,
            ['whitelabel_slot_provider_id', 'session_id'],
            ['whitelabel_slot_provider_id', 'session_id', 'slot_game_id'],
            Helper_Migration::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * This should not happen, MySQL won't allow us to revert it
     * if some duplicated session_id appear after user game change in lobby 
     */
    protected function down_gracefully(): void
    {
        Helper_Migration::updateIndex(
            self::TABLE_NAME,
            ['whitelabel_slot_provider_id', 'session_id', 'slot_game_id'],
            ['whitelabel_slot_provider_id', 'session_id'],
            Helper_Migration::INDEX_TYPE_UNIQUE
        );
    }
}
