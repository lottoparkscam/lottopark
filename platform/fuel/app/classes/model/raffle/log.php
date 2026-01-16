<?php

class Model_Raffle_Log extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'raffle_log';

    /**
     * Types of lottery log.
     */
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;

    /**
     * @param int $type
     * @param int $raffle_id
     * @param string $message
     */
    public static function add_log($type, $raffle_id, $message): void
    {
        $date = new DateTime("now", new DateTimeZone("UTC"));
        $log_entry = self::forge();
        $log_entry->set([
            'raffle_id' => $raffle_id,
            'date' => $date->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message
        ]);
        $log_entry->save();
    }
}
