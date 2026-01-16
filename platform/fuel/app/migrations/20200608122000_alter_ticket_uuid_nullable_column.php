<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;

class Alter_ticket_uuid_nullable_column
{
    public function up()
    {
        DB::query('alter table whitelabel_raffle_ticket modify uuid char(36) not null');
    }

    public function down()
    {
        DB::query('alter table whitelabel_raffle_ticket modify uuid char(36) null');
    }
}
