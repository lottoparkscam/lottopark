<?php

/** @deprecated */
class Model_Imvalap_Log extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'imvalap_log';

    // ALL THIS CONST ARE MOVED TO Helpers_General!!!!!!!!!!!
    // I left these values here at this moment
    // but it is better to collect all const in one place
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;

    /**
     *
     * @param int $type
     * @param int $whitelabel_id
     * @param int $whitelabel_user_ticket_id
     * @param int $jobid Could be null
     * @param string $message
     */
    public static function add_log(
        $type,
        $whitelabel_id,
        $whitelabel_user_ticket_id,
        int $jobid = null,
        $message
    ): void {
        $date = new DateTime("now", new DateTimeZone("UTC"));
        $var = self::forge();
        $var->set([
            'whitelabel_id' => $whitelabel_id,
            'whitelabel_user_ticket_id' => $whitelabel_user_ticket_id,
            'imvalap_job_id' => $jobid,
            'date' => $date->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message
        ]);
        $var->save();
    }
}
