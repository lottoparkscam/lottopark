<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelAffCampaign;

final class Add_Is_Casino_For_Whitelabel_Aff_Campaign extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            WhitelabelAffCampaign::get_table_name(),
            [
                'is_casino' => [
                    'type' => 'boolean',
                    'default' => 0,
                    'after' => 'campaign'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            WhitelabelAffCampaign::get_table_name(),
            [
                'is_casino',
            ]
        );
    }
}
