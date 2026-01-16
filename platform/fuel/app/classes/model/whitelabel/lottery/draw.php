<?php

/**
 *
 */
class Model_Whitelabel_Lottery_Draw extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_lottery_draw';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Fetch count of payout for wl.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return int count of the winners for specified whitelabel.
     */
    public static function count_for_whitelabel_filtered(
        array $add,
        array $params,
        int $whitelabel_id
    ): int {
        // At this moment those are empty
        // Maybe in short future results will be filtered
        $add = [];
        $params = [];
        
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_lottery_draw 
        WHERE whitelabel_lottery_draw.whitelabel_id = :whitelabel_id " .
        implode(" ", $add);
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     *
     * @return string
     */
    private static function get_full_winners_query(): string
    {
        $query = "SELECT 
            lottery_draw.id, 
            lottery.name, 
            lottery_id, 
            date_local, 
            lottery.currency_id, 
            lottery_currency.code AS lottery_currency_code,
            total_prize, 
            date_download, 
            total_winners,
            
            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . "  
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . ") AS site_winners,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket wut
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . ") AS site_ticket_winners,

            (SELECT 
                SUM(COALESCE(wutl.prize_manager, 0))
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . ") AS site_prizes_manager,
                
            (SELECT 
                SUM(COALESCE(wutl.prize_local, 0))
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . ") AS site_prizes_local,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PAIDOUT . ") AS payout_count,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket wut
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wut.payout = " . Helpers_General::TICKET_PAYOUT_PAIDOUT . ") AS payout_ticket_count,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PENDING . ") AS npayout_count,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket wut
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wut.payout = " . Helpers_General::TICKET_PAYOUT_PENDING . ") AS npayout_ticket_count,

            (SELECT 
                SUM(COALESCE(wutl.prize_manager, 0)) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PAIDOUT . ") AS payout_sum_manager,
                
            (SELECT 
                SUM(COALESCE(wutl.prize_local, 0)) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PAIDOUT . ") AS payout_sum_local,

            (SELECT 
                SUM(COALESCE(wutl.prize_manager, 0)) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PENDING . ") AS npayout_sum_manager,
            
            (SELECT 
                SUM(COALESCE(wutl.prize_local, 0)) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.payout = " . Helpers_General::TICKET_PAYOUT_PENDING . ") AS npayout_sum_local,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            LEFT JOIN lottery_type_data ltd ON ltd.id = wutl.lottery_type_data_id 
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND ltd.is_jackpot = 1) AS site_jackpot_winners,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            LEFT JOIN lottery_type_data ltd ON ltd.id = wutl.lottery_type_data_id 
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id  
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_WIN . " 
            AND ltd.type = " . Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK . ") AS site_quickpick_winners,

            (SELECT 
                SUM(COALESCE(winners, 0)) 
            FROM lottery_prize_data lpd 
            JOIN lottery_type_data ltd ON ltd.id = lpd.lottery_type_data_id
            WHERE lpd.lottery_draw_id = lottery_draw.id 
            AND ltd.is_jackpot = 1) AS total_jackpot_winners,

            (SELECT 
                SUM(COALESCE(winners, 0)) 
            FROM lottery_prize_data lpd 
            JOIN lottery_type_data ltd ON ltd.id = lpd.lottery_type_data_id
            WHERE lpd.lottery_draw_id = lottery_draw.id 
            AND ltd.type = " . Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK . ") AS total_quickpick_winners,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket_line wutl 
            JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wutl.status = " . Helpers_General::TICKET_STATUS_NO_WINNINGS . ") AS nowinners,

            (SELECT 
                COUNT(*) 
            FROM whitelabel_user_ticket wut
            WHERE wut.lottery_id = lottery_draw.lottery_id 
            AND wut.draw_date = lottery_draw.date_local 
            AND wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID . " 
            AND wut.status = " . Helpers_General::TICKET_STATUS_NO_WINNINGS . ") AS ticket_nowinners
        FROM (
            select
            whitelabel_id,
                lottery_draw_id
            from
                whitelabel_lottery_draw
            where
                whitelabel_id = :whitelabel_id
            order by
                lottery_draw_id desc
            limit
                :offset, :limit
        ) as whitelabel_lottery_draw
        JOIN lottery_draw ON lottery_draw.id = whitelabel_lottery_draw.lottery_draw_id
        JOIN lottery ON lottery.id = lottery_draw.lottery_id 
        JOIN currency lottery_currency ON lottery_currency.id = lottery.currency_id ";
        
        return $query;
    }
    
    /**
     * This is function to pull winners results and returns them as array
     *
     * TODO: Maybe should be filtered, for example by date and lottery?
     *
     * @param array $add
     * @param array $params
     * @param object $pagination
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_winners_for_whitelabel(
        array $add,
        array $params,
        $pagination,
        int $whitelabel_id
    ): array {
        // At this moment those are empty
        // Maybe in short future results will be filtered
        $add = [];
        $params = [];
        
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];
        
        $query_string = self::get_full_winners_query() .
        implode(" ", $add);

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
}
