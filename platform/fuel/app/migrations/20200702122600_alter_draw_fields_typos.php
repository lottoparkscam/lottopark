<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;

class Alter_Draw_Fields_Typos
{
    public function up()
    {
        DB::query('
        alter table raffle_draw change sale_num sale_sum decimal(15,2) unsigned null;
        alter table raffle_draw change ticket_count tickets_count int unsigned null;
        ')->execute();
    }

    public function down()
    {
        DB::query('
        alter table raffle_draw change sale_sum sale_num decimal(15,2) unsigned null;
        alter table raffle_draw change tickets_count ticket_count int unsigned null;
        ')->execute();
    }
}
