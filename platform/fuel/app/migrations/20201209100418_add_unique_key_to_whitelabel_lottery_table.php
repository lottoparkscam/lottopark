<?php

namespace Fuel\Migrations;

use Helper_Migration;

final class Add_Unique_Key_To_Whitelabel_Lottery_Table extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key(
            'whitelabel_lottery',
            ['whitelabel_id', 'lottery_id']
        );
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key(
            'whitelabel_lottery',
            ['whitelabel_id', 'lottery_id']
        );
    }
}