<?php

namespace Models;

use Orm\BelongsTo;
use Fuel\Core\Date;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;

/**
 * @property int $id
 * @property int $raffle_id
 * @property int $raffle_rule_id
 * @property int $currency_id
 * @property int $draw_no
 * @property bool $is_synchronized
 * @property Date $date
 * @property string $numbers
 * @property bool $is_calculated
 * @property float $sale_sum
 * @property float $prize_total
 * @property int $lines_won_count
 * @property int $tickets_count
 *
 * @property BelongsTo|Raffle $raffle
 * @property BelongsTo|RaffleRule $rule
 * @property BelongsTo|Currency $currency
 */
class RaffleDraw extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_draw';

    protected static $_properties = [
        'id',
        'raffle_id',
        'raffle_rule_id',
        'currency_id',
        'draw_no',
        'date',
        'numbers',
        'is_calculated' => ['default' => false],
        'is_synchronized' => ['default' => false],
        'sale_sum',
        'prize_total',
        'lines_won_count' => ['default' => 0],
        'tickets_count',
    ];

    protected static array $_belongs_to = [
        'raffle' => [
            'key_from' => 'raffle_id',
            'model_to' => Raffle::class,
            'key_to' => 'id',
            'cascade_save' => true,
        ],
        'rule' => [
            'key_from' => 'raffle_rule_id',
            'model_to' => RaffleRule::class,
            'key_to' => 'id',
        ],
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'raffle_id' => self::CAST_INT,
        'raffle_rule_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'date' => self::CAST_DATETIME,
        'draw_no' => self::CAST_INT,
        'is_synchronized' => self::CAST_BOOL,
        'is_calculated' => self::CAST_BOOL,
        'sale_sum' => self::CAST_FLOAT,
        'prize_total' => self::CAST_FLOAT,
        'lines_won_count' => self::CAST_INT,
        'tickets_count' => self::CAST_INT,
    ];

    /**
     * @param int $raffle_id
     *
     * @return self[]
     */
    public function get_draws_by_raffle(int $raffle_id): array
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_Where('raffle_id', $raffle_id),
            new Model_Orm_Criteria_Where('is_synchronized', true),
            new Model_Orm_Criteria_Order('date', 'desc')
        ])->get_results();
    }

    public function check_raffle_draw_exists(string $raffle_slug, int $draw_no): bool
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('raffle'),
            new Model_Orm_Criteria_Where('raffle.slug', $raffle_slug),
            new Model_Orm_Criteria_Where('draw_no', $draw_no),
        ])->has_results();
    }

    /**
     * @param int $raffle_id
     * @param string $draw_date
     *
     * @return array|static[]
     */
    public function find_draws_by_date(int $raffle_id, string $draw_date): array
    {
        $this->push_criteria(new Model_Orm_Criteria_Where('raffle_id', $raffle_id));
        $this->push_criteria(new Model_Orm_Criteria_Where('date', $draw_date));
        return $this->get_results();
    }

    public function mark_draw_sync(string $raffle_slug, array $dates, bool $is_synchronized): void
    {
        $this->push_criteria(new Model_Orm_Criteria_With_Relation('raffle'));
        $this->push_criteria(new Model_Orm_Criteria_Where('raffle.slug', $raffle_slug));
        $this->push_criteria(new Model_Orm_Criteria_Where('date', $dates, 'in'));
        $draws = $this->get_results();

        foreach ($draws as $draw) {
            $draw->is_synchronized = $is_synchronized;
            $draw->store($draw);
        }
    }

    public function count_unsynced_draws(string $raffle_slug)
    {
        $this->push_criteria(new Model_Orm_Criteria_With_Relation('raffle'));
        $this->push_criteria(new Model_Orm_Criteria_Where('raffle.slug', $raffle_slug));
        $this->push_criteria(new Model_Orm_Criteria_Where('is_synchronized', false));
        return $this->getCount();
    }
}
