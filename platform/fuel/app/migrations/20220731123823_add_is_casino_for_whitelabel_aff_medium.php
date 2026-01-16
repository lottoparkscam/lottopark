<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelAffMedium;

final class Add_Is_Casino_For_Whitelabel_Aff_Medium extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelAffMedium::get_table_name(),
            [
                'is_casino' => [
                    'type' => 'boolean',
                    'default' => 0,
                    'after' => 'medium'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelAffMedium::get_table_name(),
            [
                'is_casino',
            ]
        );
    }
}
