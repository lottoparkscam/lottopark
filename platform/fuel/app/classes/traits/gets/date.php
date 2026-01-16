<?php

trait Traits_Gets_Date
{

    /**
     * Get date ranges from input and prepare them for users list and reports.
     *
     * @return array date ranges (date_start, date_end).
     */
    public function prepare_dates(): array
    {
        /* Change date format */
            
        $date_start = DateTime::createFromFormat(
            "m/d/Y",
            Input::get("filter.range_start"),
            new DateTimeZone("UTC")
        );
        $date_end = DateTime::createFromFormat(
            "m/d/Y",
            Input::get("filter.range_end"),
            new DateTimeZone("UTC")
        );

        if ($date_end === false) {
            $date_end = new DateTime("now", new DateTimeZone("UTC"));
        }
            
        $date_start->setTime(0, 0, 0);
        $date_end->setTime(23, 59, 59);
        
        /* return formatted date */
            
        return [
            "date_start" => $date_start->format("Y-m-d H:i:s"),
            "date_end" => $date_end->format("Y-m-d H:i:s")
        ];
    }
}
