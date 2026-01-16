<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

class Add_wl_currency_is_visible_column extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_default_currency',
            [
                'is_visible' => [
                    'type' => 'bool',
                    'default' => true,
                    'after' => 'is_default_for_site'
                ]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_default_currency',
            [
                'is_visible'
            ]
        );
    }
}
