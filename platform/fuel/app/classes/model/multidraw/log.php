<?php

/**
 *
 */
class Model_Multidraw_Log extends Model_Model
{
    const MULTIDRAW_LOG_STATUS_SINGLE_CANCELLATION = 1;
    const MULTIDRAW_LOG_STATUS_MASSIVE_CANCELLATION = 2;
    const MULTIDRAW_LOG_STATUS_BUY = 3;
    const MULTIDRAW_LOG_STATUS_OTHER = 4;
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'multi_draw_log';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * Add new log to multidraw
     */
    public static function add_multidraw_log(
        $multi_draw_id,
        $type,
        $message,
        $data = []
    ) {
        $date = new DateTime("now", new DateTimeZone("UTC"));

        $multidraw_log = self::forge();
        $multidraw_log->set([
            'multi_draw_id' => $multi_draw_id,
            'date' => $date->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'data' => serialize($data)
        ]);

        return $multidraw_log->save();
    }
}
