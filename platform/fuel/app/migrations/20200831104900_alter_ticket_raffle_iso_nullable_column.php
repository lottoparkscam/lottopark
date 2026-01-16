<?php

namespace Fuel\Migrations;

use Fuel\Core\Cli;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;

class Alter_ticket_raffle_iso_nullable_column
{
    public function up(): void
    {
        DBUtil::modify_fields('raffle', [
            'country_iso' => ['type' => 'varchar', 'constraint' => 2, 'null' => true, 'default' => null],
        ]);
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    public function down(): void
    {
        DB::update('raffle')
            ->set(['country_iso' => ''])
            ->where('country_iso', null)
            ->execute();

        DBUtil::modify_fields('raffle', [
            'country_iso' => ['type' => 'varchar', 'constraint' => 2]
        ]);
    }
}
