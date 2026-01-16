<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Models\Lottery;
use Models\LotteryDraw;
use Oil\Exception;
use Repositories\Orm\AbstractRepository;

class LotteryDrawRepository extends AbstractRepository
{
    public const CACHE_KEY_LOTTERY_DRAW_DATES = 'model_lottery_draw.dates';
    public const CACHE_KEY_LOTTERY_DRAW_DATE_TIMES = 'model_lottery_draw.datetimes';
    public const CACHE_KEY_LOTTERY_DRAWS = 'model_lottery_draw.draws';

    public function __construct(LotteryDraw $model)
    {
        parent::__construct($model);
    }

    /** @throws Exception */
    public function getLastWinningNumbersAndBnumbersForLottery(int $lotteryId): LotteryDraw
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Select(['numbers', 'bnumbers']),
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
            new Model_Orm_Criteria_Order('id', 'desc')
        ])->getOne();
    }

    public function getLotteryDrawDateTimesForLotteryId(Lottery $lottery, string $drawDate): array
    {
        $drawDateFormatted = str_replace(' ', '', $drawDate);
        $cacheKey = self::CACHE_KEY_LOTTERY_DRAW_DATE_TIMES . '_' . $lottery->id . '_' . $drawDateFormatted;

        $lotteryDrawsDate = $this->getFromCache($cacheKey, function () use ($lottery, $drawDate) {
            /** @var mixed $query */
            $query = $this->db
                ->select($this->db->expr('DISTINCT DATE(date_local) as draw_date, 
                          DATE_FORMAT(date_local, "%Y-%m-%d %H:%i") as draw_dateTime,
                          date_local'))
                ->from(LotteryDraw::get_table_name())
                ->where('lottery_id', $lottery->id)
                ->where($this->db->expr('DATE(date_local)'), '=', $drawDate)
                ->order_by($this->db->expr('DATE(date_local)'), 'DESC')
                ->order_by('date_local', 'DESC');

            return $query->execute()->as_array();
        });

        $drawDateTimes = [];
        foreach ($lotteryDrawsDate as $draw) {
            $date = $draw['draw_date'];
            $dateTime = $draw['draw_dateTime'];
            if (!isset($drawDateTimes[$date])) {
                $drawDateTimes[$date] = [];
            }
            $drawDateTimes[$date][] = $dateTime;
        }

        return $drawDateTimes;
    }

    public function getLotteryDrawDatesForLotteryId(int $lotteryId, int $limit = 0): array
    {
        $cacheKey = self::CACHE_KEY_LOTTERY_DRAW_DATES . '_' . $lotteryId;

        $lotteryDrawDates = $this->getFromCache($cacheKey, function () use ($lotteryId, $limit) {
            /** @var mixed $query */
            $query = $this->db
                ->select($this->db->expr('DISTINCT DATE(date_local) AS date_local'))
                ->from(LotteryDraw::get_table_name())
                ->where('lottery_id', $lotteryId)
                ->order_by($this->db->expr('DATE(date_local)'), 'DESC');

            if ($limit > 0) {
                $query->limit($limit);
            }

            return $query->execute()->as_array();
        });

        return array_map(static fn ($item) => $item['date_local'], $lotteryDrawDates);
    }

    public function getLotteryDrawsByLotteryIdAndDrawDate(Lottery $lottery, string $drawDate, string $drawDateTime): array
    {
        $cacheKey = self::CACHE_KEY_LOTTERY_DRAWS . '_' . $lottery->id . '_' . $drawDate . '_' . $drawDateTime;
        $drawDateTime = $this->formatDrawDateTime($drawDateTime, $lottery->timezone);

        return $this->getFromCache($cacheKey, function () use ($lottery, $drawDate, $drawDateTime) {
            /** @var mixed $query */
            $query = $this->db
                ->selectArray([
                    'date_local',
                    'jackpot',
                    'numbers',
                    'bnumbers',
                ])
                ->from(LotteryDraw::get_table_name())
                ->where('lottery_id', $lottery->id);

            if ($drawDateTime) {
                $query->and_where('date_local', '=', $drawDateTime);
            } else {
                $query->and_where($this->db->expr('DATE(date_local)'), '=', $drawDate);
            }

            return $query->order_by('date_local', 'desc')->execute()->as_array();
        });
    }

    public function getLotteryDrawByLotteryIdAndDrawDate(Lottery $lottery, string $drawDate, string $drawDateTime): array
    {
        $drawDateTime = $this->formatDrawDateTime($drawDateTime, $lottery->timezone);

        /** @var mixed $query */
        $query = $this->db
            ->selectArray([
                'id',
                'lottery_id',
                'jackpot',
                'numbers',
                'bnumbers',
                'additional_data',
                'total_winners',
                'total_prize',
                'date_local',
                'draw_no'
            ])
            ->from(LotteryDraw::get_table_name())
            ->where('lottery_id', $lottery->id);

        if ($drawDateTime) {
            $query->and_where('date_local', '=', $drawDateTime);
        } else {
            $query->and_where($this->db->expr('DATE(date_local)'), '=', $drawDate);
        }
        $data = $query->order_by('date_local', 'desc')->execute()->as_array()[0];
        $data['timezone'] = $lottery->timezone;

        return $data;
    }

    private function formatDrawDateTime(string $drawDateTime, string $timezone): string
    {
        if ($drawDateTime) {
            $drawDateTime = Carbon::createFromFormat(
                Helpers_Time::DRAW_DATETIME_FORMAT,
                $drawDateTime,
                $timezone
            );
            return $drawDateTime->format('Y-m-d H:i');
        }

        return '';
    }

    private function getFromCache(string $cacheKey, callable $callback): mixed
    {
        try {
            return Cache::get($cacheKey);
        } catch (CacheNotFoundException $exception) {
            $result = $callback();
            Cache::set($cacheKey, $result, Helpers_Time::DAY_IN_SECONDS);
            return $result;
        }
    }

    public function getDrawDetailsForLotteryIdAndDate(int $lotteryId, string $drawDate): array
    {
        /** @var mixed $query */
        $query = $this->db
            ->selectArray([
                'jackpot',
                'numbers',
                'bnumbers',
            ])
            ->from(LotteryDraw::get_table_name())
            ->where('lottery_id', $lotteryId)
            ->where('date_local', 'like', $drawDate . '%');

        return $query->execute()->as_array();
    }
}
