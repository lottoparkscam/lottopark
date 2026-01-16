<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Whitelabel_User_Aff_Add_Btag extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_user_aff',
            [
                'btag' => [
                    'type' => 'varchar',
                    'constraint' => 500,
                    'default' => null,
                    'after' => 'whitelabel_aff_content_id',
                    'null' => true,
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_user_aff',
            [
                'btag',
            ]
        );
    }
}
