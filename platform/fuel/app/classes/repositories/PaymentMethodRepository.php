<?php

namespace Repositories;

use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder;
use Helpers\CountryHelper;
use Helpers\SanitizerHelper;
use Model_Orm_Criteria_Select;
use Models\PaymentMethod;
use Modules\CrmTable\Config;
use Repositories\Orm\AbstractRepository;

class PaymentMethodRepository extends AbstractRepository
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getAllMethods(): array
    {
        return $this->getResults();
    }

    public function getCrmAcceptanceRateReportData(Config $config, ?int $whitelabelId): array
    {
        $baseQuery = $this->getCrmAcceptanceRateReportQuery($config, $whitelabelId);
        $query = $this->db->select('*')->from([$baseQuery, 'report']);

        $codesToFilter = $this->getCountryCodesToFilterAndUnsetCountryNameFilter($config);

        $this->addFiltersToQueryBuilderOnNonaggregatedData($config->filters, $query);

        if (!empty($codesToFilter)) {
            $query->where('register_country_code', 'IN', $codesToFilter);
        }

        if (!$config->export) {
            $query->limit($config->itemsPerPage)
                ->offset($config->offset);
        }

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array();
    }

    public function countAcceptanceRateReportGroupedByMethodName(Config $config, ?int $whitelabelId): int
    {
        $query = $this->getCrmAcceptanceRateReportQueryGroupedByMethodName($config, $whitelabelId);

        /** @var Database_Query_Builder $result */
        $result = $this->db->select($this->db->expr('COUNT(*) AS count'))
            ->from([$query, 'report'])
            ->execute();
        return $result->as_array()[0]['count'] ?? 0;
    }

    public function countAcceptanceRateReport(Config $config, ?int $whitelabelId): int
    {
        $acceptanceRateQuery = $this->getCrmAcceptanceRateReportQuery($config, $whitelabelId);
        $query = $this->db->select($this->db->expr('COUNT(*) AS count'))
            ->from([$acceptanceRateQuery, 'report']);

        $codesToFilter = $this->getCountryCodesToFilterAndUnsetCountryNameFilter($config);

        $this->addFiltersToQueryBuilderOnNonaggregatedData($config->filters, $query);

        if (!empty($codesToFilter)) {
            $query->where('register_country_code', 'IN', $codesToFilter);
        }

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array()[0]['count'] ?? 0;
    }

    private function getCountryCodesToFilterAndUnsetCountryNameFilter(Config $config): array
    {
        $registerCountryFilterValue = '';
        $codesToFilter = [];
        foreach ($config->filters as $index => $filter) {
            $filterName = $filter['column'];
            $isNotRegisterCountryFilter = $filterName !== 'register_country';
            if ($isNotRegisterCountryFilter) {
                continue;
            }

            $registerCountryFilterValue = $filter['value'];
            unset($config->filters[$index]);
        }

        $isCountryNameFilterSet = !empty($registerCountryFilterValue);
        if ($isCountryNameFilterSet) {
            $codesToFilter = CountryHelper::getCountryCodesByCountryName($registerCountryFilterValue);
        }

        return $codesToFilter;
    }


    private function getCrmAcceptanceRateReportQuery(Config $config, ?int $whitelabelId): Database_Query
    {
        $checkWhitelabelId = empty($whitelabelId) ? '' : "AND wt.whitelabel_id = $whitelabelId";
        return $this->db->query("
            SELECT pm.name AS payment_method_name, 
            wu.register_country AS register_country_code,
            SUM(CASE WHEN wt.`status` = 1 THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN wt.`status` = 2 THEN 1 ELSE 0 END) AS failure_count,
            SUM(CASE WHEN wt.`status` = 1 THEN 1 ELSE 0 END) / SUM(CASE WHEN wt.`status` = 1 OR wt.`status` = 2 THEN 1 ELSE 0 END) * 100 AS success_rate
            FROM whitelabel_transaction wt
            LEFT JOIN whitelabel_payment_method wpm
            ON wt.whitelabel_payment_method_id = wpm.id
            LEFT JOIN payment_method pm
            ON wpm.payment_method_id = pm.id
            LEFT JOIN whitelabel_user wu
            ON wu.id = wt.whitelabel_user_id
            WHERE wt.date BETWEEN :fromDatetime AND :toDatetime
            " . $checkWhitelabelId . "
            GROUP BY pm.name, wu.register_country
            HAVING payment_method_name IS NOT NULL
            ORDER BY {$config->sortBy} {$config->order}
        ")
            ->parameters([
                'fromDatetime' => $config->fromDatetime,
                'toDatetime' => $config->toDatetime,
            ]);
    }

    public function getCrmAcceptanceRateReportDataGroupedByMethodName(Config $config, ?int $whitelabelId): array
    {
        $baseQuery = $this->getCrmAcceptanceRateReportQueryGroupedByMethodName($config, $whitelabelId);
        $query = $this->db->select('*')->from([$baseQuery, 'report']);

        $filters = $config->filters;

        $this->addFiltersToQueryBuilderOnNonaggregatedData($filters, $query);

        if (!$config->export) {
            $query->limit($config->itemsPerPage)
                ->offset($config->offset);
        }

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array();
    }

    private function getCrmAcceptanceRateReportQueryGroupedByMethodName(Config $config, ?int $whitelabelId): Database_Query
    {
        $checkWhitelabelId = empty($whitelabelId) ? '' : "AND wt.whitelabel_id = $whitelabelId";
        return $this->db->query("
            SELECT pm.name AS payment_method_name, 
            SUM(CASE WHEN wt.`status` = 1 THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN wt.`status` = 2 THEN 1 ELSE 0 END) AS failure_count,
            SUM(CASE WHEN wt.`status` = 1 THEN 1 ELSE 0 END) / SUM(CASE WHEN wt.`status` = 1 OR wt.`status` = 2 THEN 1 ELSE 0 END) * 100 AS success_rate,
            SUM(CASE WHEN wt.`status` = 2 THEN 1 ELSE 0 END) / SUM(CASE WHEN wt.`status` = 1 OR wt.`status` = 2 THEN 1 ELSE 0 END) * 100 AS failure_rate
            FROM whitelabel_transaction wt
            LEFT JOIN whitelabel_payment_method wpm
            ON wt.whitelabel_payment_method_id = wpm.id
            LEFT JOIN payment_method pm
            ON wpm.payment_method_id = pm.id
            LEFT JOIN whitelabel_user wu
            ON wu.id = wt.whitelabel_user_id
            WHERE wt.date BETWEEN :fromDatetime AND :toDatetime
            " . $checkWhitelabelId . "
            GROUP BY payment_method_name
            HAVING payment_method_name IS NOT NULL
            ORDER BY {$config->sortBy} {$config->order}
        ")
            ->parameters([
                'fromDatetime' => $config->fromDatetime,
                'toDatetime' => $config->toDatetime,
            ]);
    }
}
