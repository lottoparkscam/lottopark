<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Indexes_For_Whitelabel_Default_Currency extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_default_currency';
    private array $isVisibleIndex = ['is_visible'];
    private array $isDefaultForSiteIndex = ['is_default_for_site'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->isVisibleIndex);
        Helper_Migration::generateIndexKey($this->tableName, $this->isDefaultForSiteIndex);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->isVisibleIndex);
        Helper_Migration::dropIndexKey($this->tableName, $this->isDefaultForSiteIndex);
    }
}
