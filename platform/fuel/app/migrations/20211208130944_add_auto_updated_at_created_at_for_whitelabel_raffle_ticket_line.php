<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Database_Migration_Graceful;

final class Add_Auto_Updated_At_Created_At_For_Whitelabel_Raffle_Ticket_Line extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DB::query('ALTER TABLE `whitelabel_raffle_ticket_line` CHANGE `created_at` `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;')->execute();
        DB::query('ALTER TABLE `whitelabel_raffle_ticket_line` CHANGE `updated_at` `updated_at` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;')->execute();
    }

    protected function down_gracefully(): void
    {
        DB::query('ALTER TABLE `whitelabel_raffle_ticket_line` CHANGE `created_at` `created_at` DATETIME NULL DEFAULT NULL;')->execute();
        DB::query('ALTER TABLE `whitelabel_raffle_ticket_line` CHANGE `updated_at` `updated_at` DATETIME NULL DEFAULT NULL;')->execute();
    }
}
