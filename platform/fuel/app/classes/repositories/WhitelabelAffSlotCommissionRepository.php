<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffSlotCommission;
use Fuel\Core\Database_Query_Builder_Select;
use Helpers_General;
use Models\WhitelabelAff;
use Models\WhitelabelUserAff;

class WhitelabelAffSlotCommissionRepository extends AbstractRepository
{
    const AVERAGE_COMMISSION_OF_GAME_PROVIDERS = 0.15; // 15%

    public function __construct(WhitelabelAffSlotCommission $model)
    {
        parent::__construct($model);
    }

    public function hasUpdatedCasinoCommissionsOnDay(int $tier, string $dateWithoutTime): bool
    {
        return $this->recordExists(
            [
                new Model_Orm_Criteria_Where('created_at', $dateWithoutTime),
                new Model_Orm_Criteria_Where('tier', $tier)
            ]
        );
    }

    public function getCasinoCommissions(int $tier, string $dateWithoutTime, bool $skipDate = false): array
    {
        $isTier2 = $tier === Helpers_General::TYPE_TIER_SECOND;

        $querySql = "SELECT 
            IF(:tier = 1, whitelabel_aff_id, whitelabel_aff.whitelabel_aff_parent_id) AS whitelabel_aff_id,
            whitelabel_user.id AS whitelabel_user_id,";

        if ($isTier2) {
            $querySql .= 'whitelabel_aff_tier_2.whitelabel_aff_casino_group_id,';
        } else {
            $querySql .= 'whitelabel_aff.whitelabel_aff_casino_group_id,';
        }

        $querySql .= "
            whitelabel.manager_site_currency_id,
            sum_of_bets,
            sum_of_wins,
            ROUND(whitelabel.default_casino_commission_percentage_value_for_tier_1 / 100, 2) AS wl_commission_tier_1,
            ROUND(whitelabel.default_casino_commission_percentage_value_for_tier_2 / 100, 2) AS wl_commission_tier_2,
            ROUND(whitelabel_aff_casino_group.commission_percentage_value_for_tier_1 / 100, 2) AS group_commission_tier_1,
            ROUND(whitelabel_aff_casino_group.commission_percentage_value_for_tier_2 / 100, 2) AS group_commission_tier_2,
            (SELECT (IFNULL(sum_of_bets, 0) - IFNULL(sum_of_wins, 0))) AS ggr,
            (SELECT ggr - whitelabel_user.casino_bonus_balance) as ggr_without_casino_bonus_balance,
            (SELECT 
                (
                    ggr_without_casino_bonus_balance - 
                    (" . self::AVERAGE_COMMISSION_OF_GAME_PROVIDERS . " * ggr_without_casino_bonus_balance)
                ) * 
                IF(";

        if ($isTier2) {
            $querySql .= "ISNULL(whitelabel_aff_tier_2.whitelabel_aff_casino_group_id),";
        } else {
            $querySql .= "ISNULL(whitelabel_aff_casino_group_id),";
        }

        $querySql .= "IF(:tier = 1, wl_commission_tier_1, wl_commission_tier_2),
                      IF(:tier = 1, group_commission_tier_1, group_commission_tier_2)
                )
            ) ngr_commission

            FROM whitelabel_user_aff
            INNER JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id ";

        if ($isTier2) {
            $querySql .= "INNER JOIN whitelabel_aff AS whitelabel_aff_tier_2 ON whitelabel_aff_tier_2.id = whitelabel_aff.whitelabel_aff_parent_id";
        }

        $dateQuery = $skipDate ? '' : "AND created_at BETWEEN :dateStart AND :dateEnd";
        $querySql .= "
            INNER JOIN whitelabel_user ON FIND_IN_SET((SELECT GROUP_CONCAT(whitelabel_user_id) GROUP BY whitelabel_aff_id), whitelabel_user.id) != 0
            INNER JOIN (
                (SELECT
                whitelabel_user_id,
                    SUM(amount_usd) as sum_of_bets
                    FROM
                    slot_transaction FORCE INDEX (type_created_at_index)
                WHERE
                    type = 'bet' $dateQuery
                    GROUP BY
                    whitelabel_user_id)
                        ) AS sum_of_bets ON whitelabel_user.id = sum_of_bets.whitelabel_user_id
            LEFT JOIN (
                (SELECT
                whitelabel_user_id,
                    SUM(amount_usd) as sum_of_wins
                    FROM
                    slot_transaction FORCE INDEX (type_created_at_index)
                    WHERE
                    type = 'win' $dateQuery
                    GROUP BY
                    whitelabel_user_id)
                        ) AS sum_of_wins ON whitelabel_user.id = sum_of_wins.whitelabel_user_id
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user_aff.whitelabel_id ";

        if ($isTier2) {
            $querySql .= " LEFT JOIN whitelabel_aff_casino_group ON whitelabel_aff_tier_2.whitelabel_aff_casino_group_id = whitelabel_aff_casino_group.id ";
        } else {
            $querySql .= " LEFT JOIN whitelabel_aff_casino_group ON whitelabel_aff_casino_group_id = whitelabel_aff_casino_group.id ";
        }

        $querySql .= "
            WHERE whitelabel_user_aff.is_casino = 1 
            AND whitelabel_user_aff.is_deleted = 0
            AND whitelabel_user_aff.is_accepted = 1
            AND whitelabel_user_aff.is_expired = 0
            AND whitelabel_user.is_active = 1
            AND whitelabel_user.is_deleted = 0
        ";

        if ($isTier2) {
            $querySql .= ' AND whitelabel_aff.whitelabel_aff_parent_id IS NOT NULL';
        }

        $query = $this->db->query($querySql);
        $query->bind('tier', $tier);

        $dateStart = $dateWithoutTime . ' 00:00:00';
        $dateEnd = $dateWithoutTime . ' 23:59:59';
        if (!$skipDate) {
            $query->bind('dateStart', $dateStart);
            $query->bind('dateEnd', $dateEnd);
        }

        /** @phpstan-ignore-next-line */
        return $query->execute()->as_array();
    }

    public function prepareQueryForFindCasinoCommissions(
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
                'whitelabel_aff_slot_commission.whitelabel_user_id',
                'whitelabel_aff_slot_commission.daily_commission_usd',
                'whitelabel_aff_slot_commission.ggr_usd',
                'whitelabel_aff_slot_commission.whitelabel_aff_id',
                'whitelabel_user_aff.is_casino',
                $this->db->expr('whitelabel_user.email AS lead_email'),
                'whitelabel_user.name',
                $this->db->expr("CONCAT(whitelabel.prefix, 'U', whitelabel_user.token) AS user_full_name"),
                $this->db->expr('whitelabel_aff.name AS aff_name'),
                $this->db->expr('whitelabel_aff.surname AS aff_surname'),
                $this->db->expr('whitelabel_aff.login AS login'),
                $this->db->expr('DATE(whitelabel_aff_slot_commission.created_at) AS created_at'),
                'whitelabel_aff_campaign.campaign',
                'whitelabel_aff_content.content',
                'whitelabel_aff_medium.medium',
                $this->db->expr('whitelabel_currency.code AS manager_currency_code'),
                $this->db->expr('whitelabel_user_currency.code AS user_currency_code'),
                $this->db->expr('whitelabel_user_currency.rate AS user_currency_usd_rate'),
            ];
        }

        $query = $this->db->selectArray($selectFields)
            ->from(WhitelabelAffSlotCommission::get_table_name())
            ->join(WhitelabelAff::get_table_name(), 'INNER')
            ->on('whitelabel_aff_slot_commission.whitelabel_aff_id', '=', 'whitelabel_aff.id')
            ->join(WhitelabelUserAff::get_table_name(), 'LEFT')
            ->on('whitelabel_user_aff.whitelabel_user_id', '=', 'whitelabel_aff_slot_commission.whitelabel_user_id')
            ->join('whitelabel_user', 'INNER')
            ->on('whitelabel_user.id', '=', 'whitelabel_aff_slot_commission.whitelabel_user_id')
            ->join('whitelabel', 'INNER')
            ->on('whitelabel.id', '=', 'whitelabel_user_aff.whitelabel_id')
            ->join($this->db->expr('whitelabel_aff_campaign'), $joins['campaign'])
            ->on('whitelabel_aff_campaign.id', '=', 'whitelabel_user_aff.whitelabel_aff_campaign_id')
            ->join($this->db->expr('whitelabel_aff_medium'), $joins['medium'])
            ->on('whitelabel_aff_medium.id', '=', 'whitelabel_user_aff.whitelabel_aff_medium_id')
            ->join($this->db->expr('whitelabel_aff_content'), $joins['content'])
            ->on('whitelabel_aff_content.id', '=', 'whitelabel_user_aff.whitelabel_aff_content_id')
            ->join($this->db->expr('currency AS whitelabel_currency'), 'INNER')
            ->on('whitelabel.manager_site_currency_id', '=', 'whitelabel_currency.id')
            ->join($this->db->expr('currency AS whitelabel_user_currency'), 'INNER')
            ->on('whitelabel_user.currency_id', '=', 'whitelabel_user_currency.id');

        if ($isSubAffiliateTab) {
            $query->and_where('whitelabel_aff.whitelabel_aff_parent_id', $parentId);

            if (!empty($affId)) {
                $query->and_where('whitelabel_aff.id', $affId);
            }
        } else {
            if (!empty($parentId)) {
                $query->and_where('wa.whitelabel_aff_parent_id', $parentId);
            }

            if (!empty($affId)) {
                $query->and_where('whitelabel_aff_slot_commission.whitelabel_aff_id', $affId);
            }
        }

        if (!empty($dateStart)) {
            $query->and_where('created_at', '>=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $query->and_where('created_at', '<=', $dateEnd);
        }

        $whitelabelUserAffTablePrefix = 'whitelabel_user_aff';
        foreach ($columns as $column) {
            $query->and_where($whitelabelUserAffTablePrefix . '.' . $column[1], $column[2]);
        }

        $query
            ->and_where('whitelabel_aff.whitelabel_id', $whitelabelId)
            ->and_where('whitelabel_user_aff.is_accepted', true)
            ->and_where('whitelabel_user_aff.is_casino', true)
            ->order_by('whitelabel_aff_slot_commission.created_at', 'desc');

        if ($pagination) {
            $query
                ->limit($pagination->per_page)
                ->offset($pagination->offset);
        }

        return $query;
    }

    public function findCasinoCommissions(
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
        $query = $this->prepareQueryForFindCasinoCommissions(
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

    public function getCasinoCommissionCount(
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
        $query = $this->prepareQueryForFindCasinoCommissions(
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

    private function prepareQueryForFindCasinoCommissionsByReport(
        array $filters,
        ?object $pagination,
        int $whitelabelId,
        ?string $dateStart = null,
        ?string $dateEnd = null,
        string $selectType = 'result'
    ): Database_Query_Builder_Select {
        if ($selectType === 'count') {
            $selectFields = [$this->db->expr('count(*) as count')];
        } else {
            $selectFields = [
                'whitelabel_aff_slot_commission.whitelabel_user_id',
                'whitelabel_aff_slot_commission.daily_commission_usd',
                'whitelabel_aff_slot_commission.ggr_usd',
                'whitelabel_aff_slot_commission.tier',
                'whitelabel_aff_slot_commission.whitelabel_aff_id',
                'whitelabel_user_aff.is_casino',
                $this->db->expr('whitelabel_aff.email AS aff_email'),
                $this->db->expr('whitelabel_aff.token AS token'),
                $this->db->expr('whitelabel_aff.name AS name'),
                $this->db->expr('whitelabel_aff.surname AS surname'),
                $this->db->expr('whitelabel_aff.login AS login'),
                $this->db->expr('whitelabel_aff.is_confirmed AS aff_is_confirmed'),
                $this->db->expr('whitelabel_user.is_confirmed AS is_confirmed'),
                $this->db->expr('whitelabel_user.token AS lead_token'),
                $this->db->expr('whitelabel_user.email AS lead_email'),
                $this->db->expr("CONCAT(whitelabel.prefix, 'U', whitelabel_user.token) AS lead_full_name"),
                $this->db->expr('DATE(whitelabel_aff_slot_commission.created_at) AS created_at'),
                $this->db->expr('whitelabel_currency.code AS manager_currency_code'),
                $this->db->expr('whitelabel_user_currency.code AS user_currency_code'),
                $this->db->expr('whitelabel_user_currency.rate AS user_currency_usd_rate'),
            ];
        }

        $query = $this->db->selectArray($selectFields)
            ->from(WhitelabelAffSlotCommission::get_table_name())
            ->join(WhitelabelAff::get_table_name(), 'INNER')
            ->on('whitelabel_aff_slot_commission.whitelabel_aff_id', '=', 'whitelabel_aff.id')
            ->join(WhitelabelUserAff::get_table_name(), 'LEFT')
            ->on('whitelabel_user_aff.whitelabel_user_id', '=', 'whitelabel_aff_slot_commission.whitelabel_user_id')
            ->join('whitelabel_user', 'INNER')
            ->on('whitelabel_user.id', '=', 'whitelabel_aff_slot_commission.whitelabel_user_id')
            ->join('whitelabel', 'INNER')
            ->on('whitelabel.id', '=', 'whitelabel_user_aff.whitelabel_id')
            ->join($this->db->expr('currency AS whitelabel_currency'), 'INNER')
            ->on('whitelabel.manager_site_currency_id', '=', 'whitelabel_currency.id')
            ->join($this->db->expr('currency AS whitelabel_user_currency'), 'INNER')
            ->on('whitelabel_user.currency_id', '=', 'whitelabel_user_currency.id');

        $query
            ->and_where('whitelabel_aff.whitelabel_id', $whitelabelId)
            ->and_where('whitelabel_user_aff.is_accepted', true)
            ->and_where('whitelabel_user_aff.is_casino', true)
            ->order_by('whitelabel_aff_slot_commission.created_at', 'desc');

        foreach ($filters as $filter) {
            $query->and_where($filter[0], 'LIKE', $filter[1]);
        }

        if (!empty($dateStart)) {
            $query->and_where('whitelabel_aff_slot_commission.created_at', '>=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $query->and_where('whitelabel_aff_slot_commission.created_at', '<=', $dateEnd);
        }

        if ($pagination) {
            $query
                ->limit($pagination->per_page)
                ->offset($pagination->offset);
        }

        return $query;
    }

    public function findCasinoCommissionsByReport(
        array $filters,
        ?object $pagination,
        int $whitelabelId,
        ?string $dateStart = null,
        ?string $dateEnd = null,
    ) {
        $query = $this->prepareQueryForFindCasinoCommissionsByReport(
            $filters,
            $pagination,
            $whitelabelId,
            $dateStart,
            $dateEnd
        );

        /** @phpstan-ignore-next-line */
        return $query->execute()->as_array();
    }

    public function getCasinoCommissionCountByReport(
        array $filters,
        ?object $pagination,
        int $whitelabelId,
        ?string $dateStart = null,
        ?string $dateEnd = null
    ): int {
        $query = $this->prepareQueryForFindCasinoCommissionsByReport(
            $filters,
            $pagination,
            $whitelabelId,
            $dateStart,
            $dateEnd,
            'count'
        );

        return $query->execute()[0]['count'] ?? 0;
    }

    public function insert(array $casinoCommission, int $tier, $dateWithoutTime): void
    {
        $commission = new WhitelabelAffSlotCommission();
        $commission->whitelabelAffId = $casinoCommission['whitelabel_aff_id'];
        $commission->whitelabelUserId = $casinoCommission['whitelabel_user_id'];
        $commission->tier = $tier;
        $commission->createdAt = $dateWithoutTime;
        $commission->dailyCommissionUsd = $casinoCommission['ngr_commission'];
        $commission->ggrUsd = $casinoCommission['ggr_without_casino_bonus_balance'];

        $commission->save();
    }
}
