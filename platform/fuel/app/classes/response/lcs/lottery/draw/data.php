<?php

/**
 * Response Lcs Lottery Draw Data.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-11-21
 * Time: 15:39:17
 * @property-read string $next_draw_date yyyy-mm-dd hh:ii:ss e.g '2018-12-01 10:00:00'
 * @property-read string $next_next_draw_date yyyy-mm-dd hh:ii:ss e.g '2018-12-01 10:00:00'
 * @property-read string $timezone e.g. 'America/Lima'
 * @property-read string $jackpot decimal (15,2) e.g. '100000.20'
 * @property-read string[] $next_draw_datetime date and time part from next_draw_date
 * @property-read string $next_draw_datetime['date'] date part from next_draw_date e.g '2018-12-01'
 * @property-read string $next_draw_datetime['time'] time part from next_draw_date e.g '10:00:00'
 * @property-read string $next_draw_date_localized yyyy-mm-dd hh:ii:ss e.g '2018-12-01 10:00:00'
 * @property-read string[] $next_draw_datetime_localized date and time part from next_draw_date
 * @property-read string $next_draw_datetime_localized['date'] date part from next_draw_date e.g '2018-12-01'
 * @property-read string $next_draw_datetime_localized['time'] time part from next_draw_date e.g '10:00:00'
 */
final class Response_Lcs_Lottery_Draw_Data extends Response_Base
{

    protected $validator_class = Validator_Lcs_Draw::class;

    public function define_additional_fields(...$args): void
    {
        $lottery_timezone = &$args[0];
        $this->attributes['next_draw_datetime'] = Helpers_Time::datetimeToDateAndTime($this->next_draw_date);
        $next_draw_date_instance = new DateTime($this->next_draw_date, new DateTimeZone($this->timezone));
        $next_draw_date_instance->setTimezone(new DateTimeZone($lottery_timezone));
        $this->attributes['next_draw_date_localized'] = $next_draw_date_instance->format(Helpers_Time::DATETIME_FORMAT);
        $this->attributes['next_draw_datetime_localized'] = Helpers_Time::datetimeToDateAndTime($this->next_draw_date_localized);
    }
}
