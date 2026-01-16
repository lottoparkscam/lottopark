<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelUserPromoCode;

final class Add_Used_At_Field_To_Whitelabel_User_Promo_Code extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelUserPromoCode::get_table_name(),
            [
                'type' => [
                    'type' => 'tinyint',
                    'constraint' => 1,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null
                ],
                'used_at' => [
                    'type' => 'datetime',
                    'null' => true,
                    'default' => null,
                    'after' => 'type'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelUserPromoCode::get_table_name(),
            [
                'type', 'used_at',
            ]
        );
    }
}
