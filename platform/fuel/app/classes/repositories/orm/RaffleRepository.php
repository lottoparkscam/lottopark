<?php

namespace Repositories\Orm;

use Helpers_General;
use Models\{
    Raffle,
    RaffleDraw
};
use Classes\Orm\{
    AbstractOrmModel,
    Criteria\Model_Orm_Criteria_Where
};
use Fuel\Core\{
    DB,
    Database_Result
};
use Exception;

/**
 * @method findByIsEnabled(bool $true)
 * @method ?Raffle findOneBySlug(string $slug)
 * @method findOneBySlug(string $slug)
 */
class RaffleRepository extends AbstractRepository
{
    public function __construct(Raffle $model)
    {
        parent::__construct($model);
    }

    public function getAllRafflesForWhitelabelShort(int $whitelabelId): array
    {
        $query = $this->db->selectArray(['r.id', 'r.name', 'r.slug'])
            ->from(['whitelabel_raffle', 'wr'])
            ->join(['raffle', 'r'], 'INNER')->on('wr.raffle_id', '=', 'r.id')
            ->where('wr.whitelabel_id', $whitelabelId)
            ->where('r.is_enabled', true)
            ->where('r.is_sell_enabled', true)
            ->where('wr.is_enabled', true)
            ->order_by('r.id');

        /** @var Database_Result */
        $result = $query->execute();

        return $result->as_array();
    }

    /**
     *
     * @param string $value
     * @param string $field
     * @throws Exception
     */
    public function enableSellIfNotTemporaryDisabled(string $value, string $field = 'slug'): void
    {
        # todo ST: 1 Unable to use raw db update due transaction issue
        # https://trello.com/c/xnoluowJ/1315-models-orm-db-nie-uznaj%C4%85-transakcji

        /** @var Raffle $r */
        $r = $this->pushCriteria(new Model_Orm_Criteria_Where($field, $value))->getOne();
        $r->enableSellIfNotTemporaryDisabled();
        $this->save($r);
    }

    public function updateRaffleDetailsByDraw(string $slug, RaffleDraw $draw): void
    {
        /** @var Raffle $r */
        $r = $this->pushCriteria(new Model_Orm_Criteria_Where('slug', $slug))->getOne();

        $r->last_draw_date = $draw->date->format('mysql');
        $r->last_draw_date_utc = $draw->date->format('mysql', 'UTC');
        $r->last_prize_total = $draw->prize_total;
        $r->last_ticket_count = $draw->tickets_count;

        $this->save($r);
    }

    public function resetDrawLinesAndDisableSellWhenSoldOut(int $id): void
    {
        $pending = Helpers_General::TICKET_STATUS_PENDING;

        $this->db->query(/** @lang MySql */ " 
        SELECT COUNT(whitelabel_raffle_ticket_line.id)
        FROM whitelabel_raffle_ticket_line
            LEFT JOIN whitelabel_raffle_ticket ON whitelabel_raffle_ticket.id = whitelabel_raffle_ticket_id
        WHERE whitelabel_raffle_ticket_line.status = $pending
            AND whitelabel_raffle_ticket.raffle_id = $id
        INTO @unsynced;
        
        SELECT max_lines_per_draw
        FROM raffle_rule
        WHERE raffle_id = $id
        INTO @max;
        
        select IF(
           MOD(@unsynced, @max) = 0 AND @unsynced != 0,
           @max,
           MOD(@unsynced, @max)
        ) INTO @pool;
        
        UPDATE raffle SET draw_lines_count = IFNULL(@pool, 0), is_sell_enabled = is_sell_limitation_enabled = 0 AND (@unsynced = 0 OR @pool != @max) WHERE id = $id;
        ", DB::INSERT)->execute();
    }

    /**
     * @param string $slug
     * @return AbstractOrmModel|Raffle
     */
    public function getBySlug(string $slug)
    {
        return $this->pushCriteria(new Model_Orm_Criteria_Where('slug', $slug))->getOne();
    }
}
