<?php

namespace Repositories;

use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\DB;
use Helpers_Crm_General;
use Helpers_Time;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Modules\CrmTable\Config;
use Orm\RecordNotFound;
use Repositories\Orm\AbstractRepository;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Models\WhitelabelWithdrawal;

class WhitelabelWithdrawalRepository  extends AbstractRepository
{
    private const CACHE_BASE_KEY_WHITELABEL_WITHDRAWAL = 'whitelabel_withdrawal_methods';

    public function __construct(WhitelabelWithdrawal $model)
    {
        parent::__construct($model);
    }

    /**
     * V1 type whitelabels use withdrawal methods from LottoPark ID 1
     * - these V1 do not have own database entries in whitelabel_withdrawal!
     * V2 type whitelabels have own withdrawal methods defined
     */
    private static function getWhitelabelIdForWhitelabelType(array $whitelabel): int
    {
        return (int) $whitelabel['type'] === 2 ? $whitelabel['id'] : 1;
    }

    /**
     * Get cached withdrawal methods for specific whitelabel and casino
     * Cached for 24 hours
     * Key examples:
     * - whitelabel_withdrawal_methods1
     * - whitelabel_withdrawal_methods2_casino
     * - whitelabel_withdrawal_methods{WHITELABEL_ID}_{OPTIONAL_IF_CASINO}
     */
    public function getAvailableCachedMethodsForWhitelabel(array $whitelabel, bool $isCasino = false): array
    {
        $whitelabelId = $this->getWhitelabelIdForWhitelabelType($whitelabel);

        $cacheKey = self::CACHE_BASE_KEY_WHITELABEL_WITHDRAWAL . $whitelabelId;
        if ($isCasino) {
            $cacheKey .= '_casino';
        }

        try {
            $withdrawalMethods = Cache::get($cacheKey);
        } catch (CacheNotFoundException $exception) {
            $withdrawalMethods = $this->getAvailableMethodsForWhitelabel($whitelabelId, $isCasino);
            Cache::set($cacheKey, $withdrawalMethods, Helpers_Time::DAY_IN_SECONDS);
        }

        if (empty($withdrawalMethods)) {
            return [];
        }

        return $withdrawalMethods;
    }

    /**
     * Gets whitelabel withdrawal methods based on show and show_casino flags
     */
    private function getAvailableMethodsForWhitelabel(int $whitelabelId, bool $isCasino): array
    {
        $showField = $isCasino ? 'show_casino' : 'show';
        $this->pushCriterias([
                                 new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
                                 new Model_Orm_Criteria_Order('id', 'asc'),
                                 new Model_Orm_Criteria_Where($showField, true),
                             ]);

        return $this->getResults();
    }

    /**
     * Should not be cached, it is used to get or verify that user selects a valid method
     * @param bool $isCasino is required as you are trying to retrieve valid result for casino or not
     * @throws RecordNotFound when unable to find record with given criteria - should be caught intentionally
     */
    public function getAvailableMethodByIdForWhitelabel(int $withdrawalId, array $whitelabel, bool $isCasino): WhitelabelWithdrawal
    {
        $whitelabelId = $this->getWhitelabelIdForWhitelabelType($whitelabel);

        $showField = $isCasino ? 'show_casino' : 'show';
        $this->pushCriterias([
                                 new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
                                 new Model_Orm_Criteria_Where('withdrawal_id', $withdrawalId),
                                 new Model_Orm_Criteria_Where($showField, true),
                             ]);

        /** @var WhitelabelWithdrawal $whitelabelWithdrawal */
        $whitelabelWithdrawal = $this->getOne();
        return $whitelabelWithdrawal;
    }

    public function getWithdrawalRequestNumbersGroupedPerMethod(
        ?int $whitelabelId,
        bool $isCasino,
        Config $crmTableConfig,
        string $startDate,
        string $endDate
    ): int
    {
        $baseQuery = $this->getWithdrawalRequestGroupedPerMethodQuery(
            $whitelabelId,
            $isCasino,
            $startDate,
            $endDate,
        );

        /** @var Database_Query_Builder $query */
        $query = $this->db->select('*')->from([$baseQuery, 'report']);
        $this->addFiltersToQueryBuilderOnAggregatedData($crmTableConfig->filters, $query);

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return count($result->as_array());
    }

    public function getWithdrawalRequestGroupedPerMethod(
        ?int $whitelabelId,
        bool $isCasino,
        Config $crmTableConfig,
        string $startDate,
        string $endDate
    ): array
    {
        $baseQuery = $this->getWithdrawalRequestGroupedPerMethodQuery(
            $whitelabelId,
            $isCasino,
            $startDate,
            $endDate,
        );

        $query = $this->db->select('*')->from([$baseQuery, 'report']);
        $this->addFiltersToQueryBuilderOnAggregatedData($crmTableConfig->filters, $query);

        $query->limit($crmTableConfig->itemsPerPage)
            ->offset($crmTableConfig->offset);

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array();
    }

    private function getWithdrawalRequestGroupedPerMethodQuery(
        ?int $whitelabelId,
        bool $isCasino,
        string $startDate,
        string $endDate,
    ): Database_Query
    {
        $whitelabelExists = !empty($whitelabelId);
        $whereWhitelabelId = $whitelabelExists ? "AND wr.whitelabel_id = $whitelabelId" : '';
        $amountToSum = $whitelabelExists ? 'wr.amount_manager' : 'wr.amount_usd';
        $isCasino = $isCasino ? 1 : 0;

        return DB::query("
        SELECT withdrawal.name,
            SUM(CASE WHEN wr.status = 0 THEN 1 ELSE 0 END) / count(*) * 100 AS pending_rate,
            SUM(CASE WHEN wr.status = 0 THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN wr.status = 0 THEN $amountToSum ELSE 0 END) AS pending_amount,
            SUM(CASE WHEN wr.status = 1 THEN 1 ELSE 0 END) / count(*) * 100 AS approved_rate,
            SUM(CASE WHEN wr.status = 1 THEN 1 ELSE 0 END) AS approved_count,
            SUM(CASE WHEN wr.status = 1 THEN $amountToSum ELSE 0 END) AS approved_amount,
            SUM(CASE WHEN wr.status = 2 THEN 1 ELSE 0 END) / count(*) * 100 AS declined_rate,
            SUM(CASE WHEN wr.status = 2 THEN 1 ELSE 0 END) AS declined_count,
            SUM(CASE WHEN wr.status = 2 THEN $amountToSum ELSE 0 END) AS declined_amount,
            SUM(CASE WHEN wr.status = 3 THEN 1 ELSE 0 END) / count(*) * 100 AS canceled_rate,
            SUM(CASE WHEN wr.status = 3 THEN 1 ELSE 0 END) AS canceled_count,
            SUM(CASE WHEN wr.status = 3 THEN $amountToSum ELSE 0 END) AS canceled_amount
        FROM withdrawal_request as wr 
        LEFT JOIN withdrawal ON wr.withdrawal_id = withdrawal.id
        WHERE wr.is_casino = $isCasino 
        AND wr.date >= '$startDate' AND wr.date <= '$endDate'
        $whereWhitelabelId GROUP BY wr.withdrawal_id
        ");
    }

    public function getWithdrawalSumForCrmDateRange(?int $whitelabelId, string $startDate, string $endDate, int $status = 0): float
    {
        $sum = 0;

        $query = DB::select(DB::expr('SUM(amount_manager) as sum'))->from('withdrawal_request')
            ->where('status', '=', $status)
            ->and_where('date', '>=', Helpers_Crm_General::prepare_start_date($startDate))
            ->and_where('date', '<=', Helpers_Crm_General::prepare_end_date($endDate));

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $result = $query->execute();
        if (!empty($result[0]['sum'])) {
            $sum = $result[0]['sum'];
        }
        
        return $sum;
    }
}
