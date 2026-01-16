<?php

namespace Repositories;

use Classes\Orm\Criteria\CriteriaOrderByCustom;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Container;
use Fuel\Core\Database_Query;
use Fuel\Core\Database_Result;
use Helpers\SlotHelper;
use Models\SlotTransaction;
use Models\Whitelabel;
use Models\WhitelabelSlotProvider;
use Modules\CrmTable\Config;
use Repositories\Orm\AbstractRepository;
use Models\SlotGame;

/**
 * @method SlotGame|null findOneByUuid(string $uuid)
 * @method SlotGame findOneById(int $id)
 */
class SlotGameRepository extends AbstractRepository
{
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private ?Whitelabel $whitelabel;

    public function __construct(
        SlotGame $model,
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository
    ) {
        parent::__construct($model);
        $this->whitelabelSlotProviderRepository = $whitelabelSlotProviderRepository;
        $this->whitelabel = Container::get('whitelabel') ?? null;
    }

    /** @return SlotGame[] */
    public function findGamesBySlotProviderId(int $slotProviderId, array $subProvidersNames): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('provider', $subProvidersNames, 'IN'),
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProviderId),
        ]);

        return $this->getResults();
    }

    /**
     * This function only prepares query to get enabled games. It's separated in order to avoid repeating
     * the code in functions: 
     * findEnabledGamesCountForWhitelabelBySlotProvidersIds
     * findEnabledGamesForSlotProviders
     */
    public function prepareQueryToGetEnabledGames(int $whitelabelId, array $slotProvidersIds, bool $isMobile = true): SlotGameRepository
    {
        $allowedSubprovidersForThisWhitelabel = $this->whitelabelSlotProviderRepository->getAllowedSubprovidersNamesByWhitelabelId($whitelabelId);
        /** @var WhitelabelSlotGameOrderRepository $whitelabelSlotGameOrderRepository  */
        $whitelabelSlotGameOrderRepository = Container::get(WhitelabelSlotGameOrderRepository::class);
        $gamesOrder = $whitelabelSlotGameOrderRepository->findOneByWhitelabelId($whitelabelId)->orderJson['homepage'] ?? [];

        $gamesOrderNew = SlotHelper::getGamesIdsSortedByGameOrder($gamesOrder);

        $criterias = [
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProvidersIds, 'IN'),
            new Model_Orm_Criteria_Where('is_deleted', false),
        ];

        if (!empty($gamesOrderNew)) {
            $criterias[] = new CriteriaOrderByCustom('id', $gamesOrderNew);
        }

        $criterias[] = new Model_Orm_Criteria_Order('name', 'ASC');

        $allowedGameUuids = !empty($this->whitelabel) ?
            SlotHelper::getAllowedGameUuids($this->whitelabel->domain) :
            true;
        $whitelabelHasSpecialFilters = is_array($allowedGameUuids);
        if ($whitelabelHasSpecialFilters) {
            $criterias[] = new Model_Orm_Criteria_Where('uuid', $allowedGameUuids, 'IN');
        }

        if (!empty($allowedSubprovidersForThisWhitelabel)) {
            $criterias[] = new Model_Orm_Criteria_Where('provider', $allowedSubprovidersForThisWhitelabel, 'IN');
        } else {
            // if wl doesn't have allowedSubprovider we shouldnt return any game
            $criterias[] = new Model_Orm_Criteria_Where('provider', null, 'IS');
        }

        $isNotMobile = !$isMobile;
        if ($isNotMobile) {
            $criterias[] = new Model_Orm_Criteria_Where('name', '%mobile%', 'NOT LIKE');
        } else {
            $criterias[] = new Model_Orm_Criteria_Where('is_mobile', $isMobile);
        }

        $this->pushCriterias($criterias);

        return $this;
    }

    /** @param int[] $slotProvidersIds */
    public function findEnabledGamesCountForWhitelabelBySlotProvidersIds(int $whitelabelId, array $slotProvidersIds, bool $isMobile = true): int
    {
        if (empty($slotProvidersIds)) {
            return 0;
        }

        return $this->prepareQueryToGetEnabledGames($whitelabelId, $slotProvidersIds, $isMobile)->getCount() ?? 0;
    }

    /**
     * @param int[] $slotProvidersIds
     * @return SlotGame[]
     */
    public function findEnabledGamesForSlotProviders(int $whitelabelId, array $slotProvidersIds, bool $isMobile = true, int $limit = 0, int $offset = 0): array
    {
        if (empty($slotProvidersIds)) {
            return [];
        }

        return $this->prepareQueryToGetEnabledGames($whitelabelId, $slotProvidersIds, $isMobile)->getResults($limit, $offset) ?? [];
    }

    /**
     * @param bool $shouldEnable - if true, search games to enable, if false search games to disable
     * @return SlotGame[]
     */
    private function findGamesWhichStatusHasToBeChanged(array $uuids, int $slotProviderId, bool $shouldEnable, array $subProvidersNames): array
    {
        $operator = 'NOT IN';
        if ($shouldEnable) {
            $operator = 'IN';
        }
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['uuid']),
            new Model_Orm_Criteria_Where('uuid', $uuids, $operator),
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProviderId),
        ]);

        if (!empty($subProvidersNames)) {
            // we change status only from received subproviders
            $this->pushCriteria(new Model_Orm_Criteria_Where('provider', $subProvidersNames, 'IN'));
        }

        return $this->getResults();
    }

    /**
     * @param string[] $uuids
     * @return SlotGame[]
     */
    public function findGamesToEnable(array $uuids, int $slotProviderId, array $subProvidersNames): array
    {
        return $this->findGamesWhichStatusHasToBeChanged($uuids, $slotProviderId, true, $subProvidersNames);
    }

    /**
     * @param string[] $uuids
     * @return SlotGame[]
     */
    public function findGamesToDisable(array $uuids, int $slotProviderId, array $subProvidersNames): array
    {
        return $this->findGamesWhichStatusHasToBeChanged($uuids, $slotProviderId, false, $subProvidersNames);
    }

    public function countWithAnyBetTransaction(Config $crmTableConfig, int $whitelabelId): int
    {
        $query = $this->db->select($this->db->expr('sg.name'))
            ->from([$this->model::get_table_name(), 'sg'])
            ->join(
                [SlotTransaction::get_table_name(), $this->db->expr( 'st FORCE INDEX (action_created_at_index)')],
                'LEFT'
            )
            ->on('st.slot_game_id', '=', 'sg.id')
            ->where('st.action', '=', SlotTransaction::ACTION_BET)
            ->and_where('st.id', 'IS NOT', null)
            ->and_where('st.created_at', 'BETWEEN', [$crmTableConfig->fromDatetime, $crmTableConfig->toDatetime]);

        /** $whitelabelId is 0 for Superadmin in CRM */
        if ($whitelabelId > 0) {
            $query->join([WhitelabelSlotProvider::get_table_name(), 'wsp'])
                ->on('wsp.id', '=', 'st.whitelabel_slot_provider_id')
                ->and_where('wsp.whitelabel_id', '=', $whitelabelId);
        }

        $this->addFiltersToQueryBuilderOnNonaggregatedData($this->prepareFiltersForQuery($crmTableConfig->filters), $query);
        $query->group_by('sg.name');

        /** @var Database_Result $results */
        $results = $query->execute();

        return count($results->as_array());
    }

    public function countProvidersWithAnyBetTransaction(Config $crmTableConfig, int $whitelabelId): int
    {
        $query = $this->db->select($this->db->expr('COUNT(DISTINCT provider) AS count'))
            ->from([$this->model::get_table_name(), 'sg'])
            ->join(
                [SlotTransaction::get_table_name(), $this->db->expr( 'st FORCE INDEX (action_created_at_index)')],
                'LEFT'
            )
            ->on('st.slot_game_id', '=', 'sg.id')
            ->where('st.action', '=', SlotTransaction::ACTION_BET)
            ->and_where('st.id', 'IS NOT', null)
            ->and_where('st.created_at', 'BETWEEN', [$crmTableConfig->fromDatetime, $crmTableConfig->toDatetime]);

        if ($whitelabelId > 0) {
            $query->join([WhitelabelSlotProvider::get_table_name(), 'wsp'])
                ->on('wsp.id', '=', 'st.whitelabel_slot_provider_id')
                ->and_where('wsp.whitelabel_id', '=', $whitelabelId);
        }

        $this->addFiltersToQueryBuilderOnNonaggregatedData($this->prepareFiltersForQuery($crmTableConfig->filters), $query);

        /** @var Database_Result $results */
        $results = $query->execute();

        return $results->as_array()[0]['count'];
    }

    private function prepareFiltersForQuery(array $filters): array
    {
        $newFilters = [];
        foreach ($filters as $filter) {
            switch ($filter['column']) {
                case 'game_name':
                    $filter['column'] = 'sg.name';
                    $newFilters[] = $filter;
                    break;
            }
        }
        return $newFilters;
    }

    public function findOneByUuidAndSlotProviderId(string $uuid, int $slotProviderId): ?SlotGame
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('uuid', $uuid),
            new Model_Orm_Criteria_Where('slot_provider_id', $slotProviderId)
        ]);

        return $this->findOne();
    }

    /** @return SlotGame[] */
    public function findEnabledGamesByWhitelabelId(int $whitelabelId): array
    {
        $idsOfEnabledSlotProvidersForThisWhitelabel = $this->whitelabelSlotProviderRepository->findIdsOfEnabledSlotProvidersByWhitelabelId($whitelabelId);

        if (empty($idsOfEnabledSlotProvidersForThisWhitelabel)) {
            return [];
        }

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('is_deleted', false),
            new Model_Orm_Criteria_Where('slot_provider_id', $idsOfEnabledSlotProvidersForThisWhitelabel, 'IN'),
        ]);

        return $this->getResults() ?? [];
    }

    /** @return SlotGame[] syntax: gameId => gameData */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $games = $this->pushCriteria(
            new Model_Orm_Criteria_Where('id', $ids, 'IN')
        )->getResults() ?? [];

        if (empty($games)) {
            return [];
        }

        return array_column($games, null, 'id');
    }

    public function getNumberOfGames(): int
    {
        /** @var Database_Query $query */
        $query = $this->db->select($this->db->expr('count(*) as numberOfGames'))
            ->from($this->model::get_table_name());
        /** @phpstan-ignore-next-line */
        $queryResults = $query->execute()->as_array();
        return $queryResults[0]['numberOfGames'];
    }
}
