<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Language extends \Model_Model
{
    /**
     *
     * @var array
     */
    protected static $_table_name = 'whitelabel_language';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_language.whitelabellanguages",
        "model_whitelabel_language.admin_whitelabellanguages"
    ];

    /**
     *
     * @param array $whitelabel
     * @param int $cache_list_id
     * @return array
     */
    public static function get_whitelabel_languages(
        array $whitelabel,
        int $cache_list_id = 0
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expired_time = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[$cache_list_id];
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $key .= '.'.$whitelabel['id'];
        }
        
        $query = "SELECT 
            language.*, 
            whitelabel_language.id AS wl_lang_id,
            whitelabel_language.currency_id
        FROM whitelabel_language 
        JOIN language ON language.id = whitelabel_language.language_id
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel_language.whitelabel_id = :whitelabel_id";
        }
                
        $query .= " ORDER BY language.id";
        
        $db = DB::query($query);
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $db->param(":whitelabel_id", $whitelabel['id']);
        }
            
        try {
            try {
                $whitelabel_languages = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $whitelabel_languages = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $whitelabel_languages, $expired_time);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $whitelabel_languages = $db->execute()->as_array();
        }
        
        return $whitelabel_languages;
    }
    
    /**
     * Fetch count of whitelabel_languages filtered.
     *
     * @param int $whitelabel_id whitelabel id
     * @return int count of the whitelabel languages rows.
     */
    public static function get_counted_for_whitelabel(int $whitelabel_id = null): int
    {
        $params = [];
        // add non global params
        if (!empty($whitelabel_id)) {
            $params[] = [
                ":whitelabel_id",
                $whitelabel_id
            ];
        }
        
        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_language 
        WHERE whitelabel_id = :whitelabel_id ";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
}
