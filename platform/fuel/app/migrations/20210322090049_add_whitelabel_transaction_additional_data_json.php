<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Whitelabel_Transaction_Additional_Data_Json extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            'whitelabel_transaction',
            [
                'additional_data_json' => [
                    'type' => 'json',
                    'after' => 'additional_data',
                    'null' => true
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            'whitelabel_transaction',
            [
                'additional_data_json',
            ]
        );
    }
}
