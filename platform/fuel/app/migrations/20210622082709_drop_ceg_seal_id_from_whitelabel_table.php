<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Drop_Ceg_Seal_Id_From_Whitelabel_Table extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'ceg_seal_id'
        ]);
    }

    protected function down_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'ceg_seal_id' => [
                'type' => 'varchar',
                'constraint' => 45,
                'null' => true,
                'default' => null
            ]
        ]);
    }
}
