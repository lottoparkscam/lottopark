<?php

use Carbon\Carbon;

final class Helpers_Time
{
    /**
     * Iso week days.
     */
    const ISO_WEEK_DAYS = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun',
    ];

    const PHP_FULL_WEEK_DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    const HOUR_IN_SECONDS = 3600;
    const DAY_IN_SECONDS = 86400;
    const HALF_DAY_IN_SECONDS = 43200;
    const MINUTE_IN_SECONDS = 60;
    const HOUR_IN_MILLISECONDS = 3600000;
    const TENTH_OF_SECOND_IN_MICRO = 100000;
    const YEAR_IN_SECONDS = 31536000;
    public const TEN_MINUTES_IN_SECONDS = 600;
    public const HALF_HOUR_IN_SECONDS = 1800;
    public const ELEVEN_HOURS_IN_MINUTES = 660;
    public const WEEK_IN_SECONDS = 604800;

    /**
     * Default datetime format.
     */
    const DATETIME_FORMAT = self::DATE_FORMAT . ' ' . self::TIME_FORMAT;

    /**
     *  Datetime format without seconds.
     */
    const DATETIME_NO_SECONDS_FORMAT = self::DATE_FORMAT . ' H:i';

    /**
     * Default date format.
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * Default time format.
     */
    const TIME_FORMAT = 'H:i:s';
    
    /**
     * Default timezone.
     */
    const TIMEZONE = 'UTC';
    const DRAW_DATETIME_FORMAT = 'YmdHi';
    const UNIX_EPOCH_DATETIME = "1970-01-01 00:00:00";
    public const DRAWDATE_FORMAT = 'D H:i';
    public const ACTIVATION_HASH_SEND_DATE_CARBON_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get now date in string.
     *
     * @param string $format
     * @param string $timezone NOTE: null will default to TIMEZONE
     *
     * @return string
     */
    public static function now(string $format = self::DATETIME_FORMAT, string $timezone = null): string
    {
        return (new DateTime('now', new DateTimeZone($timezone ?: self::TIMEZONE)))
            ->format($format);
    }

    /**
     * Parse date in provided timezone into date in UTC timezone.
     * NOTE: input in strings, output in string.
     *
     * @param string $date
     * @param string $timezone
     * @param string $format
     *
     * @return string date in UTC
     */
    public static function utc(string $date, string $timezone, string $format = self::DATETIME_FORMAT): string
    {
        return (new DateTime($date, new DateTimeZone($timezone)))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format($format);
    }

    /**
     * Explode string date time to separate date and time.
     *
     * @param string $datetime  in
     * @param string $separator string separating date and time. NOTE: won't work for empty.
     *
     * @return string[] 'date', 'time' indexes
     */
    public static function datetimeToDateAndTime(string $datetime, string $separator = ' '): array
    {
        $separator_index = strpos($datetime, $separator);

        return [
            'date' => substr($datetime, 0, $separator_index),
            'time' => substr($datetime, $separator_index + 1), // excluding separator character
        ];
    }

    public static function migration_time_prefix(): string
    {
        return self::now('YmdHis');
    }

    public static function generate_draw_days_json(string $draw_days, string $draw_hour): string
    {
        $json = [];
        $days = explode(',', $draw_days);
        foreach ($days as $day) {
            $json[] = self::ISO_WEEK_DAYS[$day] . " " . date('H:i', strtotime($draw_hour));
        }

        return json_encode($json);
    }

    public static function generateDrawDatesArray($startTime, $interval, $drawsDaily): array
    {
        $drawDates = [];
        foreach (self::ISO_WEEK_DAYS as $day) {
            $date = Carbon::parse($day . ' ' . $startTime);
            for ($i = 0; $i < $drawsDaily; $i++) {
                $date->addMinutes($i == 0 ? 0 : $interval); // we don't want to add interval to start time!
                $drawDates[] = $date->format('D H:i');
            }
        }
        return $drawDates;
    }

    public static function generateMultipleDrawsPerDayJson(array $drawDays, array $drawTimes): string
    {
        $data = [];
        foreach ($drawDays as $day) {
            foreach ($drawTimes as $time) {
                $data[] = self::ISO_WEEK_DAYS[$day] . ' ' . date('H:i', strtotime($time));
            }
        }

        return json_encode($data);
    }

    public static function createFromDatetimeStr(string $datetime, string $timezone = 'UTC'): Carbon
    {
        return Carbon::createFromFormat(
            self::DATETIME_FORMAT,
            $datetime,
            new DateTimeZone($timezone)
        );
    }

    public static function sort_weekdays_asc(array &$weekdays): void
    {
        usort($weekdays, (function ($a, $b) {
            // We use next week to sort the dates because we don't want it to sort relative to now
            $a_carbon = Carbon::parse($a . " next week");
            $b_carbon = Carbon::parse($b . " next week");

            return $b_carbon->lessThan($a_carbon);
        }));
    }

    /**
     * @deprecated
     * Backward compatibility
     * NOTE: if function draw dates has more than one draw per day only one day will be returned e.g. Mon 12:00, Mon 16:00 will be returned as 1
     * @return int[] iso days
     */
    public static function drawDateToDrawDays(array $drawDates): array
    {
        $drawDays = [];
        foreach ($drawDates as $drawDate) {
            $drawDays[Carbon::parse($drawDate)->dayOfWeekIso] = null;
        }
        return array_keys($drawDays); // trick to avoid duplicates
    }

    /** 
     * @return null when timezone doesn't exist 
     * @throws Exception when timezone does not exist
     */
    public static function getTimestampBeforeXMinutes(int $minutes, string $timezone = 'UTC', string $fromTimestamp = ''): string
    {
        try {
            $currentDate = !empty($fromTimestamp) ? Carbon::createFromFormat('Y-m-d H:i:s', $fromTimestamp, $timezone) : Carbon::now($timezone);
        } catch (Exception $e) {
            throw new Exception("Provided timezone $timezone does not exist.");
        }

        return $currentDate->subMinutes($minutes)->toDateTimeString();
    }

    public static function getFirstDateInCurrentMonth(string $timezone): string
    {
        $now = new Carbon($timezone);
        return $now->format('Y-m-01');
    }

    public static function getLastDateInCurrentMonth(string $timezone): string
    {
        $now = new Carbon($timezone);
        return $now->format('Y-m-t');
    }

    /**
     * @param Carbon $mainDate is checked if happened before $secondDate
     * 
     * - This function supports different timezones during compare.
     * - If dates are equal it returns false. 
     */
    public static function isDateBeforeDate(Carbon $mainDate, Carbon $secondDate): bool
    {
        return $mainDate->lt($secondDate);
    }

    /** @param ?Carbon $carbon gets now() if carbon not provided */
    public static function getTimestampWithTimezone(?Carbon $carbon = null): string
    {
        if (empty($carbon)) {
            $carbon = Carbon::now();
        }

        $timestamp = $carbon->format(self::DATETIME_FORMAT);
        $timezone = $carbon->timezone->getName() ?? '';

        return "$timestamp $timezone";
    }

    public static function isDayPassed(string $date): bool
    {
        $date = new Carbon($date);
        return $date->diffInDays() >= 1;
    }

    public static function getCarbonDateNowInUtc(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    public static function isDayNotPassed(string $date): bool
    {
        return !self::isDayPassed($date);
    }

    public static function getFirstDateTimeOfCurrentMonth(): string
    {
        $carbonFirstDayOfCurrentMonth = new Carbon('first day of this month');
        return $carbonFirstDayOfCurrentMonth->startOfMonth()->format(Helpers_Time::DATETIME_FORMAT);
    }

    public static function getLastDateTimeOfCurrentMonth(): string
    {
        $carbonLastDayOfCurrentMonth = new Carbon('last day of this month');
        return $carbonLastDayOfCurrentMonth->endOfMonth()->format(Helpers_Time::DATETIME_FORMAT);
    }
}
