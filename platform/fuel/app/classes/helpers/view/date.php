<?php


use Carbon\Carbon;
use Fuel\Core\Date;

class Helpers_View_Date
{
    public function format_date(string $timezone, $date = null, bool $short = false): string
    {
        if (empty($date)) {
            return '-';
        }

        return Lotto_View::format_date_without_timezone(
            $date,
            IntlDateFormatter::LONG,
            $short ? IntlDateFormatter::NONE : IntlDateFormatter::SHORT,
            null,
            $timezone ?: date_default_timezone_get(),
            $timezone ?: date_default_timezone_get()
        );
    }

    public static function format_date_for_user_timezone(?string $date = null, ?string $source_timezone = null): string
    {
        if (empty($date)) {
            return '-';
        }
        
        if (empty($source_timezone)) {
            $source_timezone = date_default_timezone_get();
        }

        return Lotto_View::format_date(
            $date,
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            $source_timezone,
            true
        );
    }
}