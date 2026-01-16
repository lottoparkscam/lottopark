<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Aff_Can_Create_Sub_Affiliates_Field_In_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'aff_can_create_sub_affiliates' => [
                'type' => 'bool',
                'null' => false,
                'default' => false,
                'after' => 'aff_auto_create_on_register'
            ]
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'aff_can_create_sub_affiliates'
        ]);
    }
}