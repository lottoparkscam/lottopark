<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_New_Type_To_Enum_For_Social_Type extends Database_Migration_Graceful
{
    private const TABLE = 'social_type';

    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            self::TABLE,
            [
                'type' => ['constraint' => "'facebook', 'google'", 'type' => 'enum'],
            ],
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            self::TABLE,
            [
                'type' => ['constraint' => "'facebook'", 'type' => 'enum'],
            ],
        );
    }
}
