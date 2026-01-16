<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Helper_Migration;

final class Add_Index_For_Whitelabel_Raffle_Ticket extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_raffle_ticket';
    private string $whitelabelRaffleTicketLineTableName = 'whitelabel_raffle_ticket_line';
    private array $index = ['status'];

    protected function up_gracefully(): void
    {
        Helper_Migration::generateIndexKey($this->tableName, $this->index);
        Helper_Migration::generateIndexKey($this->whitelabelRaffleTicketLineTableName, $this->index);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey($this->tableName, $this->index);
        Helper_Migration::dropIndexKey($this->whitelabelRaffleTicketLineTableName, $this->index);
    }
}
