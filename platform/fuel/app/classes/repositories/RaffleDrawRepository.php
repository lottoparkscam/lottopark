<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Limit;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Fuel\Core\Database_Query_Builder;
use Models\RaffleDraw;
use Repositories\Orm\AbstractRepository;

class RaffleDrawRepository extends AbstractRepository
{
    public function __construct(RaffleDraw $model)
    {
        parent::__construct($model);
    }

    public function getDrawsByRaffleId(int $raffleId): array
    {
        $query = $this->db->selectArray(['id', 'date', 'draw_no'])
            ->from(RaffleDraw::get_table_name())
            ->where('raffle_id', $raffleId)
            ->and_where('is_synchronized', true)
            ->order_by('date', 'desc');

        /** @var mixed $draws */
        $draws = $query->execute();

        return $draws->as_array();
    }

    public function findLastDrawByRaffleId(int $raffleId): ?RaffleDraw
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('raffle_id', $raffleId),
            new Model_Orm_Criteria_Order('date', 'desc'),
            new Model_Orm_Criteria_Limit(1),
        ]);

        return $this->findOne();
    }

    public function getLastDrawNumberByRaffleSlug(string $raffleSlug): int
    {
        $query = $this->db->select('draw_no')
            ->from(RaffleDraw::get_table_name())
            ->join('raffle', 'LEFT')
            ->on('raffle.id', '=', 'raffle_id')
            ->where('raffle.slug', $raffleSlug)
            ->order_by('draw_no', 'desc')
            ->limit(1);

        /** @var mixed $results */
        $results = $query->execute();
        $results = $results->as_array();

        return isset($results[0]) ? $results[0]['draw_no'] : 0;
    }
}
