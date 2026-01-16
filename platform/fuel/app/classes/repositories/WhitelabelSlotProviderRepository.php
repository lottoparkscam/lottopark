<?php

namespace Repositories;

use Container;
use Fuel\Core\Database_Query_Builder;
use Helpers\ArrayHelper;
use Models\SlotGame;
use Wrappers\Db;
use Helpers_Time;
use Helpers\SlotHelper;
use Helpers_Crm_General;
use Models\Whitelabel;
use Models\SlotTransaction;
use Modules\CrmTable\Config;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Models\WhitelabelSlotProvider;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Repositories\Orm\AbstractRepository;

/**
 * @method WhitelabelSlotProvider findOneById(int $whitelabelSlotProviderId)
 */
class WhitelabelSlotProviderRepository extends AbstractRepository
{
    protected Db $db;
    private ?Whitelabel $whitelabel;

    public function __construct(WhitelabelSlotProvider $model, Db $db)
    {
        parent::__construct($model);
        $this->db = $db;
        $this->whitelabel = Container::get('whitelabel') ?? null;
    }

    /**
     * This function gets allowed subproviders based on:
     * - user's country
     * - wl enabled providers [received from .._sync_slot_games task]
     */
    public function getAllowedSubprovidersNamesByWhitelabelId(int $whitelabelId): array
    {
        $restrictedProvidersForThisCountry = SlotHelper::getRestrictedProviders();
        $enabledSubprovidersForThisWhitelabel = $this->getAllSubprovidersNamesByWhitelabelId($whitelabelId);

        return array_diff($enabledSubprovidersForThisWhitelabel, $restrictedProvidersForThisCountry);
    }

    public function getAllSubprovidersNamesByWhitelabelId(int $whitelabelId): array
    {
        $subproviderNamesQuery = $this->db->select('name')
        ->from($this->model::get_table_name())
            ->join('whitelabel_slot_provider_subprovider', 'INNER')
            ->on('whitelabel_slot_provider_subprovider.whitelabel_slot_provider_id', '=', 'whitelabel_slot_provider.id')
            ->join('slot_subprovider', 'LEFT')
            ->on('slot_subprovider.id', '=', 'whitelabel_slot_provider_subprovider.slot_subprovider_id')
            ->where('whitelabel_slot_provider.is_enabled', '=', true)
            ->where('whitelabel_slot_provider_subprovider.is_enabled', '=', true)
            ->and_where('whitelabel_id', '=', $whitelabelId);

        $allowedGameUuids  = !empty($this->whitelabel) ?
            SlotHelper::getAllowedGameUuids($this->whitelabel->domain) :
            true;
        $hasSpecificFilters = is_array($allowedGameUuids);
        if ($hasSpecificFilters) {
            /** @var Database_Query_Builder $providerNamesQuery */
            $providerNamesQuery = $this->db->select($this->db->expr('DISTINCT provider'))
                ->from(SlotGame::get_table_name())
                ->where('uuid', 'IN', $allowedGameUuids)
                ->execute();
            $allowedProviderNames = array_column($providerNamesQuery->as_array(), 'provider');
            $subproviderNamesQuery->where('slot_subprovider.name', 'IN', $allowedProviderNames);
        }

        $subproviderNames = $subproviderNamesQuery->order_by('name')->execute();

        if (empty($subproviderNames) || is_int($subproviderNames)) {
            return [];
        }

        return ArrayHelper::createSingleArrayFromValue($subproviderNames->as_array(), 'name');    
    }

    public function findByWhitelabelAndProviderSlug(int $whitelabelId, string $providerSlug): ?WhitelabelSlotProvider
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('slot_provider'),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('slot_provider.slug', $providerSlug)
        ]);

        return $this->findOne();
    }

    /** @return ?WhitelabelSlotProvider[] */
    public function findAllSlotProviders(bool $enabled = true): ?array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('slot_provider'),
            new Model_Orm_Criteria_Where('is_enabled', $enabled)
        ]);

        return $this->getResults();
    }

    /** @return int[] */
    public function findEnabledSlotProvidersIdsByWhitelabelId(int $whitelabelId): array
    {
        $this->pushCriterias([
        new Model_Orm_Criteria_Select(['slot_provider_id']),
        new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        new Model_Orm_Criteria_Where('is_enabled', 1)
        ]);
        return $this->getResultsForSingleField();
    }

    /** @return array{data: array} */
    public function findLimitsPerV2WhitelabelWithPagination(Config $config, float $currencyUsdRate): array
    {
        $timezone = (new SlotTransaction())->getTimezoneForField('createdAt');
        $fromDatetime = Helpers_Crm_General::prepare_start_date(Helpers_Time::getFirstDateInCurrentMonth($timezone));
        $toDatetime = Helpers_Crm_General::prepare_end_date(Helpers_Time::getLastDateInCurrentMonth($timezone));

        $query = $this->db->selectArray([
            [$this->db->expr(
                "ROUND((max_monthly_money_around_usd - SUM(slot_transaction.amount_usd)) * $currencyUsdRate, 2)"
            ), 'left_limit'],
            [$this->db->expr('whitelabel.name'), 'whitelabel_name']
        ])
            ->from(WhitelabelSlotProvider::get_table_name())
            ->join('whitelabel', 'LEFT')
            ->on('whitelabel.id', '=', 'whitelabel_slot_provider.whitelabel_id')
            ->join('slot_transaction', 'LEFT')
            ->on('slot_transaction.whitelabel_slot_provider_id', '=', 'whitelabel_slot_provider.id')
            ->and_on('slot_transaction.action', '=', $this->db->expr("'" . SlotTransaction::ACTION_BET . "'"))
            ->and_on('slot_transaction.created_at', 'BETWEEN', $this->db->expr("'$fromDatetime' AND '$toDatetime'"))
        ->where('whitelabel.type', '=', Whitelabel::TYPE_V2);

        if ($config->isNotSuperadminView) {
            $query->and_where('whitelabel.id', '=', $config->whitelabelId);
        }

        $query->group_by('whitelabel_slot_provider.id');

        $filters = array_map(function ($filter) {
            switch ($filter['column']) {
                case 'left_limit_usd':
                    $filter['column'] = 'max_monthly_money_around_usd';
                    break;
            }
            return $filter;
        }, $config->filters);

        $this->addFiltersToQueryBuilderOnAggregatedData($filters, $query);

        if (!empty($config->sortBy)) {
            $query->order_by($config->sortBy, $config->order);
        }

        /** @var mixed $result */
        $result = $query->offset($config->offset)
            ->limit($config->itemsPerPage)
            ->execute();

        /** @var array{whitelabel_name: string, left_limit_usd: float} $data */
        $data = $result->as_array();

        return [
            'data' => $data
        ];
    }

    public function getWhitelabelLimitInUsd(int $whitelabelId): float
    {
        WhitelabelSlotProvider::flush_cache();
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['max_monthly_money_around_usd']),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ]);

        /** @var WhitelabelSlotProvider $whitelabelSlotProvider */
        $whitelabelSlotProvider = $this->findOne();

        return $whitelabelSlotProvider->maxMonthlyMoneyAroundUsd;
    }

    public function findIdBySlotProviderIdAndWhitelabelId(int $slotProviderId, int $whitelabelId): int
    {
        $slotProvider = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProviderId),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ])->findOne();

        if (!empty($slotProvider->id)) {
            return $slotProvider->id;
        }

        return 0;
    }

    /** @return int[] */
    public function findIdsByWhitelabelId(int $whitelabelId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        $result = $this->getResultsForSingleField();
        $this->clearCache();
        return $result;
    }

    /** @front function for CRM, display only enabled slot providers */
    public function findIdsOfEnabledSlotProvidersByWhitelabelId(int $whitelabelId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['slot_provider_id']),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_enabled', true),
        ]);

        return $this->getResultsForSingleField() ?? [];
    }
}
