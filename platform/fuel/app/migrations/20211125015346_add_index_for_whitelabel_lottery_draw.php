<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_For_Whitelabel_Lottery_Draw extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_lottery_draw';
    private array $indexColumns = [
        'whitelabel_id',
        'lottery_draw_id'
    ];

    protected function up_gracefully(): void
    {
        Helper_Migration::generate_unique_key($this->tableName, $this->indexColumns);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::drop_unique_key($this->tableName, $this->indexColumns);
    }
}
