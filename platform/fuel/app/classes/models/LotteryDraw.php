<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use DateTime;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $lottery_id
 * @property int $lottery_type_id
 * @property int $draw_no
 * @property DateTime|Carbon $date_download
 * @property DateTime|Carbon $date_local
 * @property float $jackpot
 * @property string $numbers
 * @property string $bnumbers
 * @property float $total_prize
 * @property float $total_winners
 * @property float $final_jackpot
 * @property bool $has_pending_tickets
 * @property string $additional_data
 *
 * @property BelongsTo|Lottery $lottery
 */
class LotteryDraw extends AbstractOrmModel
{ // TODO: {Vordis 2021-04-26 12:48:44} string casts to array: numbers, bnumbers, additional data
    protected static string $_table_name = 'lottery_draw';
    
    protected static array $_properties = [
        'id',
        'lottery_id',
        'lottery_type_id',
        'draw_no',
        'date_download',
        'date_local',
        'jackpot',
        'numbers',
        'bnumbers',
        'total_prize',
        'total_winners',
        'final_jackpot',
        'has_pending_tickets',
        'additional_data',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'lottery_type_id' => self::CAST_INT,
        'draw_no' => self::CAST_INT,
        'date_download' => self::CAST_CARBON,
        'date_local' => self::CAST_CARBON,
        'jackpot' => self::CAST_FLOAT,
        'numbers' => self::CAST_STRING,
        'bnumbers' => self::CAST_STRING,
        'total_prize' => self::CAST_FLOAT,
        'total_winners' => self::CAST_FLOAT,
        'final_jackpot' => self::CAST_FLOAT,
        'has_pending_tickets' => self::CAST_BOOL,
        'additional_data' => self::CAST_STRING,
    ];
    
    protected array $relations = [
        Lottery::class => self::BELONGS_TO
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

}