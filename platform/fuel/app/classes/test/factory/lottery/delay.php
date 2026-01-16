<?php


use Carbon\Carbon;
use Fuel\Core\DB;

/** @deprecated - use new fixtures instead */
final class Test_Factory_Lottery_Delay extends Test_Factory_Base
{
    protected function before(int $count, array $values): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();
    }

    protected function after(int $count, array $values): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=1;')
            ->execute();
    }

    protected function values(array &$values): array
    {
        return [
                'lottery_id' => 1,
                'date_local' => Carbon::now()->format(Helpers_Time::DATE_FORMAT),
                'date_delay' => Carbon::tomorrow()->format(Helpers_Time::DATE_FORMAT),
            ] + $values;
    }
}
