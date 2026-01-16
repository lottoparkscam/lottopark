<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Is_Aff_User_To_Whitelabel_Aff extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_aff',
            [
                'is_aff_user' => [
                    'type' => 'bool',
                    'null' => true,
                    'default' => null,
                    'after' => 'is_accepted'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_aff',
            [
                'is_aff_user',
            ]
        );
    }
}
