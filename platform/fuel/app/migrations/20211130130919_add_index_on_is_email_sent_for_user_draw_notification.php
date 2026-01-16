<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_On_Is_Email_Sent_For_User_Draw_Notification extends Database_Migration_Graceful
{
    private string $tableName = 'user_draw_notification';
    private array $index = ['is_email_sent'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
    }
}
