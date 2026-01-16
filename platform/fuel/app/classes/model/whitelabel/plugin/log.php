<?php

class Model_Whitelabel_Plugin_Log extends Model_Model
{

    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_plugin_log';

    /**
     *
     * @param int $type
     * @param int $whitelabel_plugin_id
     * @param string $message
     */
    public static function add_log($type, $whitelabel_plugin_id, $message): void
    {
        $date = new DateTime("now", new DateTimeZone("UTC"));
        $var = self::forge();
        $var->set([
            'whitelabel_plugin_id' => $whitelabel_plugin_id,
            'date' => $date->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message
        ]);
        $var->save();
    }
}
