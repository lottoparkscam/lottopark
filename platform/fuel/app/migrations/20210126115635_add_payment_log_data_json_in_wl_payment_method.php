<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_payment_log_data_json_in_wl_payment_method extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_payment_method',
            [
                'data_json' => [
                    'type' => 'json',
                    'null' => true,
                    'after' => 'data'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_payment_method',
            [
                'data_json',
            ]
        );
    }
}
