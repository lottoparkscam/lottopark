<?php

use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\DB;

/**
 * @deprecated
 */
class Model_Raffle extends Model_Model
{
    /** @var array */
    protected static $_properties = [
        'id', 'raffle_rule_id', 'currency_id', 'name', 'country', 'country_iso', 'slug',
        'is_enabled', 'timezone', 'main_prize', 'last_draw_date', 'last_draw_date_utc',
        'next_draw_date', 'next_draw_date_utc', 'last_prize_total', 'draw_lines_count',
        'last_ticket_count'
    ];

    /** @var string */
    protected static $_table_name = 'raffle';

    /**
     * @param Closure $adjust_query optional adjustment of query. function (Database_Query_Builder_Select &$process_query): void
     * @return array */
    public static function for_whitelabel(int $whitelabel_id, Closure $adjust_query = null): array
    {
        /** @var Database_Query $database_query_builder parent */
        $database_query_builder = DB::select_array([
            'r.*',
            'rr.max_lines_per_draw',
            'rr.line_price',
            'rr.fee',
        ])
            ->from([self::$_table_name, 'r'])
            ->join(['whitelabel_raffle', 'wlr'], 'LEFT')
            ->on('wlr.raffle_id', '=', 'r.id')
            ->join(['raffle_rule', 'rr'], 'RIGHT')
            ->on('r.raffle_rule_id', '=', 'rr.id')
            ->where('r.is_enabled', '=', true)
            ->where('wlr.id', '=', $whitelabel_id);

        $should_adjust_query = $adjust_query !== null;
        if ($should_adjust_query) {
            $adjust_query($database_query_builder);
        }

        return Helpers_Cache::read_or_create($database_query_builder, function (array &$raffles): void {
            $raffles = self::group_raffles($raffles);
        }) ?: [];
    }

    /** @return array */
    public static function for_whitelabel_wl_enabled_with_currency(int $whitelabel_id): array
    {
        return self::for_whitelabel($whitelabel_id, function (Database_Query_Builder_Select $query_builder): void {
            $query_builder->select(['c.code', 'currency_code'])
                ->join(['currency', 'c'], 'RIGHT')
                ->on('r.currency_id', '=', 'c.id')
                ->where('wlr.is_enabled', '=', true);
        });
    }

    /** @return array */
    public static function group_raffles(array $raffles): array
    {
        $grouped_raffles_by_slug = [];
        $grouped_raffles_by_id = [];
        foreach ($raffles as $raffle) {
            $grouped_raffles_by_slug[$raffle['slug']] = $raffle;
            $grouped_raffles_by_id[$raffle['id']] = $raffle;
        }
        return ['__by_id' => $grouped_raffles_by_id, '__by_slug' => $grouped_raffles_by_slug];
    }

    public static function getActiveRaffleForWhitelabel(int $whitelabel_id): array
    {
        /** @var Database_Query $database_query_builder parent */
        $database_query_builder = DB::select_array([
            'r.id',
            'r.name',
            'r.slug',
            'r.is_enabled',
            'r.is_sell_enabled',
            'r.is_sell_limitation_enabled',
        ])
            ->from([self::$_table_name, 'r'])
            ->join(['whitelabel_raffle', 'wlr'], 'LEFT')
            ->on('wlr.raffle_id', '=', 'r.id')
            ->where('r.is_enabled', '=', true)
            ->where('wlr.whitelabel_id', '=', $whitelabel_id);

        return Helpers_Cache::read_or_create($database_query_builder, function (array &$raffles): void {
            $raffles = self::group_raffles($raffles);
        }) ?: [];
    }

    public static function getActiveRaffleForWhitelabelByHighestPrize(int $whitelabel_id): array
    {
        /** @var Database_Query $database_query_builder parent */
        $database_query_builder = DB::select_array([
            'r.id',
            'r.name',
            'r.slug',
            'r.is_enabled',
            'r.is_sell_enabled',
            'r.is_sell_limitation_enabled',
            'r.main_prize',
            DB::expr("'raffle' AS type"),
        ])
            ->from([self::$_table_name, 'r'])
            ->join(['whitelabel_raffle', 'wlr'], 'LEFT')
            ->on('wlr.raffle_id', '=', 'r.id')
            ->where('r.is_enabled', '=', true)
            ->where('wlr.whitelabel_id', '=', $whitelabel_id)
            ->order_by('r.main_prize', 'DESC');

        return Helpers_Cache::read_or_create($database_query_builder, function (array &$raffles): void {
            $raffles = self::group_raffles($raffles);
        }) ?: [];
    }
}
