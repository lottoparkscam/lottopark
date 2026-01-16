<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Password_Reset_Token_In_Whitelabel_Aff_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_aff',
            [
                'password_reset_hash' => [
                    'type' => 'varchar', 
                    'constraint' => 64, 
                    'null' => true, 
                    'default' => null,
                    'after' => 'salt'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_aff',
            [
                'password_reset_hash',
            ]
        );
    }
}