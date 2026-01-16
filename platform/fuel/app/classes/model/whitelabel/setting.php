<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Setting extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_setting';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_settings($whitelabel)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $settings = [];
        $query = "SELECT * 
            FROM whitelabel_setting 
            WHERE whitelabel_id = :whitelabel";
        
        $db = DB::query($query);
        $db->param(":whitelabel", $whitelabel['id']);
        
        try {
            $settings = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        $settings_arr = [];
        foreach ($settings as $setting) {
            $settings_arr[$setting['name']] = $setting['value'];
        }
        
        return $settings_arr;
    }
}
