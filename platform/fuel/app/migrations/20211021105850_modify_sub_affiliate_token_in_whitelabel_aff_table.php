<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;
use Fuel\Core\DBUtil;

final class Modify_Sub_Affiliate_Token_In_Whitelabel_Aff_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_aff',
            [
                'sub_affiliate_token' => [
                    'type' => 'varchar',
                    'constraint' => 10,
                    'null' => false,
                    'after' => 'token',
                ],
            ]
        );

        Helper_Migration::generate_unique_key(
            'whitelabel_aff',
            ['sub_affiliate_token']
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            'whitelabel_aff',
            [
                'sub_affiliate_token' => [
                    'type' => 'varchar',
                    'constraint' => 10,
                    'null' => true,
                    'default' => null,
                    'after' => 'token',
                ],
            ]
        );
        Helper_Migration::drop_unique_key(
            'whitelabel_aff',
            ['sub_affiliate_token']
        );
    }
}