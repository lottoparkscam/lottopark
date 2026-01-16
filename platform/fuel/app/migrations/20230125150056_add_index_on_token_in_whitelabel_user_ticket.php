<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Token_In_Whitelabel_User_Ticket extends Database_Migration_Graceful
{
    private const TABLE = 'whitelabel_user_ticket';
    private const COLUMNS = ['token'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey(self::TABLE, self::COLUMNS);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey(self::TABLE, self::COLUMNS);
    }
}
