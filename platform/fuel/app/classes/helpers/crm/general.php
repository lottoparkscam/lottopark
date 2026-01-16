<?php

use Models\Whitelabel;

class Helpers_Crm_General
{
    /**
     *
     * @param string $date_start
     * @return string
     */
    public static function prepare_start_date($date_start)
    {
        $startd = new DateTime($date_start);
        $starts = $startd->setTime(0, 0, 0);
        $start = $starts->format("Y-m-d H:i:s");
        
        return $start;
    }
    
    /**
     *
     * @param string $date_end
     * @return string
     */
    public static function prepare_end_date($date_end)
    {
        $endd = new DateTime($date_end);
        $ends = $endd->setTime(23, 59, 59);
        $end = $ends->format("Y-m-d H:i:s");
        
        return $end;
    }
}
