<?php

declare(strict_types=1);

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Limit;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelRaffleTicket;
use Repositories\Orm\AbstractRepository;
use Wrappers\Db;

class WhitelabelRaffleTicketRepository extends AbstractRepository
{
    public function __construct(WhitelabelRaffleTicket $model, Db $db)
    {
        parent::__construct($model);
        $this->db = $db;
    }

    public function getSumsBonusesForReports(
        int $whitelabelId,
        array $dates,
        string $language = null,
        string $country = null
    ): object
    {
        $sumExpression = $this->db->expr('COALESCE(SUM(bonus_cost_manager), 0.00) AS sum_bonus_cost_manager');

        $query = $this->db
            ->select($sumExpression)
            ->from($this->db->expr('whitelabel_raffle_ticket'))
            ->join($this->db->expr('whitelabel_user'))
            ->on('whitelabel_user.id', '=', 'whitelabel_raffle_ticket.whitelabel_user_id')
            ->where('whitelabel_raffle_ticket.whitelabel_id', $whitelabelId)
            ->and_where('whitelabel_raffle_ticket.whitelabel_transaction_id', 'IS', null)
            ->and_where('whitelabel_raffle_ticket.created_at', 'BETWEEN', $dates);

        if ($language !== null) {
            $query->and_where('whitelabel_user.language_id', $language);
        }

        if ($country !== null) {
            $query->and_where('whitelabel_user.country', $country);
        }

        return $query->execute();
    }

    public function getSumsBonusesForAdminReports(
        array $dates,
        int $whitelabelId = null,
        int $whitelabelType = null,
        string $language = null,
        string $country = null,
        bool $isFullReport = false
    ): object
    {
        $sumExpression = [
            $this->db->expr('COALESCE(SUM(bonus_cost_manager), 0.00) AS sum_bonus_cost_manager'),
            $this->db->expr('COALESCE(SUM(bonus_cost_usd), 0.00) AS sum_bonus_cost_usd')
        ];

        $query = $this->db
            ->selectArray($sumExpression)
            ->from($this->db->expr('whitelabel_raffle_ticket'))
            ->join($this->db->expr('whitelabel_user'),'INNER')
            ->on('whitelabel_user.id', '=', 'whitelabel_raffle_ticket.whitelabel_user_id')
            ->join($this->db->expr('whitelabel'),'INNER')
            ->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->and_where('whitelabel_raffle_ticket.created_at', 'BETWEEN', $dates)
            ->and_where('whitelabel_raffle_ticket.whitelabel_transaction_id', 'IS', null);

        if ($whitelabelId !== null) {
            $query->and_where('whitelabel_raffle_ticket.whitelabel_id', $whitelabelId);
        }

        if ($whitelabelType !== null) {
            $query->and_where('whitelabel.type', $whitelabelType);
        }

        if ($language !== null) {
            $query->and_where('whitelabel_user.language_id', $language);
        }

        if ($country !== null) {
            $query->and_where('whitelabel_user.country', $country);
        }

        if (!$isFullReport && $whitelabelId === null) {
            $query->and_where('whitelabel.is_report', 1);
        }

        return $query->execute();
    }

    public function getLastByRaffleId(int $raffleId): ?WhitelabelRaffleTicket
    {
        $this->pushCriterias([
           new Model_Orm_Criteria_Where('raffle_id', $raffleId),
           new Model_Orm_Criteria_Order('created_at', 'desc'),
           new Model_Orm_Criteria_Limit(1),
        ]);

        return $this->findOne();
    }
}
