<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Sub_Affiliate_Token_Field_In_Whitelabel_Aff_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_aff', [
            'sub_affiliate_token' => [
                'type' => 'varchar',
                'constraint' => 10,
                'null' => true,
                'default' => null,
                'after' => 'token',
            ],
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_aff', [
            'sub_affiliate_token',
        ]);
    }
}
