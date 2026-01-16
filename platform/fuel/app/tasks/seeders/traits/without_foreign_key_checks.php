<?php

use Fuel\Core\DB;

/**
 * Run seed without Foreign Key Checks
 * NOTE: it will work if seeder class or any other trait DOESN'T override execute class!
 */
trait Without_Foreign_Key_Checks
{
    public function execute(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();
        parent::execute();
        DB::query('SET FOREIGN_KEY_CHECKS=1;')
            ->execute();
    }
}
