<?php


trait Model_Traits_For_Lottery
{
    public static function for_lottery(int $lottery_id, array $columns = ['*']): array
    {
        $columns_glued = implode(',', $columns);
        $db_query = DB::select($columns_glued)
            ->from(static::$_table_name)
            ->where('lottery_id', $lottery_id);

        return Helpers_Cache::read_or_create($db_query) ?: [];
    }
}