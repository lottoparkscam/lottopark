<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
class Model_Language extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'language';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_language.alllanguages",
        "model_language.allcodes"
    ];

    /**
     *
     * @param array $languages
     * @return array
     */
    private static function prepare_all_languages($languages)
    {
        $nlanguages = [];
        foreach ($languages as $language) {
            $nlanguages[$language['id']] = $language;
        }
        return $nlanguages;
    }

    /**
     *
     * @return array
     */
    public static function get_all_languages()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $query = "SELECT * FROM language ORDER BY id";
        $languages = null;
        $key = self::$cache_list[0];
        $expired_time = Helpers_Whitelabel::get_expired_time();
        
        $db = DB::query($query);
        
        try {
            try {
                $languages = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $languages = self::prepare_all_languages($db->execute()->as_array());
                Lotto_Helper::set_cache($key, $languages, $expired_time);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            $languages = self::prepare_all_languages($db->execute()->as_array());
        }
        
        return $languages;
    }
    
    /**
     *
     * @return array
     */
    public static function get_all_locale_codes()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $query = "SELECT code FROM language ORDER BY code";
        $codes = null;
        $key = self::$cache_list[1];
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        
        $db = DB::query($query);
        
        try {
            try {
                $codes = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                /** @var object $db */
                $codes = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $codes, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            /** @var object $db */
            $codes = $db->execute()->as_array();
        }
        
        return $codes;
    }
}
