<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Plugin extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_plugin';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];

    public static function get_plugin_by_name($whitelabel_id, $name)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $plugin = [];
        $query = "SELECT * 
            FROM whitelabel_plugin 
            WHERE whitelabel_id = :whitelabel AND plugin = :plugin LIMIT 1";

        $db = DB::query($query);
        $db->param(":whitelabel", $whitelabel_id);
        $db->param(":plugin", $name);

        try {
            $plugin = $db->execute()->as_array();
            if (isset($plugin[0]['options'])) {
                $plugin[0]['options'] = json_decode($plugin[0]['options']);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        return $plugin[0] ?? null;
    }
}
