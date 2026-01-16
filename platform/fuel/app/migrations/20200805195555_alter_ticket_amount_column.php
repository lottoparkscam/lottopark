<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Alter_Ticket_Amount_Column extends \Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_raffle_ticket', [
            'amount' => ['type' => 'decimal', 'constraint' => [8, 2], 'unsigned' => true]
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_raffle_ticket', [
            'amount' => ['type' => 'decimal', 'constraint' => [5, 2], 'unsigned' => true]
        ]);
    }
}
