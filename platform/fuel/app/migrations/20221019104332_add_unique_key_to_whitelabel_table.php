<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;
use Models\Whitelabel;

final class Add_Unique_Key_To_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key(
            Whitelabel::get_table_name(),
            ['prefix']
        );
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key(
            Whitelabel::get_table_name(),
            ['prefix']
        );
    }
}
