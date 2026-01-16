<?php

namespace Repositories\Aff;

use Fuel\Core\Database_Query_Builder_Select;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffCommission;
use Helpers_General;

class WhitelabelAffCommissionRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffCommission $whitelabelAffCommission)
    {
        parent::__construct($whitelabelAffCommission);
    }

    public function getCommissionCount(
        array $columns,
        array $joins,
        ?object $pagination,
        ?int $parentId,
        ?int $affId,
        bool $isSubAffiliateTab,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        int $whitelabelId
    ): int {
        $query = $this->prepareQueryForFindCommissions(
            $columns,
            $joins,
            $pagination,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            $dateStart,
            $dateEnd,
            $whitelabelId,
            'count'
        );

        return $query->execute()[0]['count'] ?? 0;
    }

    public function prepareQueryForFindCommissions(
        array $columns,
        array $joins,
        ?object $pagination,
        ?int $parentId,
        ?int $affId,
        bool $isSubAffiliateTab,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        int $whitelabelId,
        string $selectType = 'result'
    ): Database_Query_Builder_Select {

        if ($selectType === 'count') {
            $selectFields = [$this->db->expr('count(*) as count')];
        } else {
            $selectFields = [
                'wac.whitelabel_aff_id',
                'wu.token',
                $this->db->expr('wt.type AS ttype'),
                $this->db->expr('wt.token AS ttoken'),
                'wt.amount',
                'wt.amount_payment',
                'wt.amount_manager',
                'wt.cost',
                'wt.cost_manager',
                'wt.payment_cost',
                'wt.payment_cost_manager',
                'wt.income',
                'wt.income_manager',
                'wt.date_confirmed',
                'wac.commission',
                'wac.commission_manager',
                'wac.type',
                'wac.tier',
                $this->db->expr('payment_cur.id AS payment_currency_id'),
                $this->db->expr('payment_cur.code AS payment_currency_code'),
                $this->db->expr('payment_cur.rate AS payment_currency_rate'),
                $this->db->expr('user_cur.id AS user_currency_id'),
                $this->db->expr('user_cur.code AS user_currency_code'),
                $this->db->expr('user_cur.rate AS user_currency_rate'),
                $this->db->expr('manager_cur.id AS manager_currency_id'),
                $this->db->expr('manager_cur.code AS manager_currency_code'),
                $this->db->expr('manager_cur.rate AS manager_currency_rate'),
                $this->db->expr('wu.name AS lead_name'),
                $this->db->expr('wu.surname AS lead_surname'),
                $this->db->expr('wu.email AS lead_email'),
                'waca.campaign',
                'wua.is_casino',
                'wam.medium',
                'waco.content'
            ];
        }

        $query = $this->db
            ->selectArray($selectFields)
            ->from($this->db->expr('whitelabel_aff_commission as wac'))
            ->join($this->db->expr('whitelabel_aff as wa'), 'INNER')
            ->on('wa.id', '=', 'wac.whitelabel_aff_id')
            ->join($this->db->expr('whitelabel as wl'), 'INNER')
            ->on('wl.id', '=', 'wa.whitelabel_id')
            ->join($this->db->expr('currency as manager_cur'), 'INNER')
            ->on('manager_cur.id', '=', 'wl.manager_site_currency_id')
            ->join($this->db->expr('whitelabel_transaction as wt'), 'LEFT')
            ->on('wt.id', '=', 'wac.whitelabel_transaction_id')
            ->join($this->db->expr('currency as payment_cur'), 'LEFT')
            ->on('payment_cur.id', '=', 'wt.payment_currency_id')
            ->join($this->db->expr('currency as user_cur'), 'LEFT')
            ->on('user_cur.id', '=', 'wt.currency_id')
            ->join($this->db->expr('whitelabel_user as wu'), 'LEFT')
            ->on('wu.id', '=', 'wt.whitelabel_user_id')
            ->join($this->db->expr('whitelabel_user_aff as wua'), 'LEFT');

        if ($isSubAffiliateTab) {
            $query->on('wua.whitelabel_user_id', '=', 'wu.id');
        } else {
            $query->on('wua.whitelabel_user_id', '=', 'wu.id')
                ->on('wua.whitelabel_aff_id', '=', 'wac.whitelabel_aff_id');
        }

        $query
            ->join($this->db->expr('whitelabel_aff_campaign as waca'), $joins['campaign'])
            ->on('waca.id', '=', 'wua.whitelabel_aff_campaign_id')
            ->on('waca.whitelabel_aff_id', '=', 'wua.whitelabel_aff_id')
            ->join($this->db->expr('whitelabel_aff_medium as wam'), $joins['medium'])
            ->on('wam.id', '=', 'wua.whitelabel_aff_medium_id')
            ->on('wam.whitelabel_aff_id', '=', 'wua.whitelabel_aff_id')
            ->join($this->db->expr('whitelabel_aff_content as waco'), $joins['content'])
            ->on('waco.id', '=', 'wua.whitelabel_aff_content_id')
            ->on('waco.whitelabel_aff_id', '=', 'wua.whitelabel_aff_id');

        $query->and_where('wua.is_casino', false);

        if ($isSubAffiliateTab) {
            $query->and_where('wac.whitelabel_aff_id', $parentId);
            $query->and_where('wac.tier', Helpers_General::TYPE_TIER_SECOND);

            if (!empty($affId)) {
                $query->and_where('wac.whitelabel_user_aff_id', $affId);
            }
        } else {
            if (!empty($parentId)) {
                $query->and_where('wa.whitelabel_aff_parent_id', $parentId);
            }

            if (!empty($affId)) {
                $query->and_where('wac.whitelabel_aff_id', $affId);
            }
        }

        if (!empty($dateStart)) {
            $query->and_where('date_confirmed', '>=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $query->and_where('date_confirmed', '<=', $dateEnd);
        }

        $whitelabelUserAffTablePrefix = 'wua';
        foreach ($columns as $column) {
            $query->and_where($whitelabelUserAffTablePrefix . '.' . $column[1], $column[2]);
        }

        $query
            ->and_where('wa.whitelabel_id', $whitelabelId)
            ->and_where('wua.is_accepted', true)
            ->and_where('wac.is_accepted', true)
            ->and_where('wt.type', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('wt.status', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->order_by('date_confirmed', 'desc')
            ->order_by('wt.type', 'desc');

        if ($pagination) {
            $query
                ->limit($pagination->per_page)
                ->offset($pagination->offset);
        }

        $query->distinct();
        return $query;
    }

    public function findCommissions(
        array $columns,
        array $joins,
        ?object $pagination,
        ?int $parentId,
        ?int $affId,
        bool $isSubAffiliateTab,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        int $whitelabelId
    ): object {
        $query = $this->prepareQueryForFindCommissions(
            $columns,
            $joins,
            $pagination,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            $dateStart,
            $dateEnd,
            $whitelabelId
        );

        return $query->execute();
    }

    public function getLastUserTransactionCommission($user): float
    {
        $query = $this->db
            ->select('commission_usd')
            ->from($this->db->expr('whitelabel_aff_commission'))
            ->join($this->db->expr('whitelabel_transaction'), 'LEFT')
            ->on('whitelabel_transaction.id', '=', 'whitelabel_aff_commission.whitelabel_transaction_id')
            ->where('whitelabel_transaction.whitelabel_user_id', '=', $user->id)
            ->and_where('whitelabel_transaction.date', '=', $user->last_purchase_date)
            ->execute();
        return $query[0]['commission_usd'];
    }
}
