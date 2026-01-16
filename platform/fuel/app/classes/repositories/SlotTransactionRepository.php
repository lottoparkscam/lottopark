<?php

namespace Repositories;

use Carbon\Carbon;
use Exception;
use Fuel\Core\Database_Query_Builder;
use Helpers_Crm_General;
use Helpers_Time;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Modules\CrmTable\Config;
use Repositories\Orm\AbstractRepository;
use Models\SlotTransaction;
use Container;
use Modules\Mediacle\Models\SalesDataSlotTransactionModelAdapter;

class SlotTransactionRepository extends AbstractRepository
{
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;

    public function __construct(SlotTransaction $model)
    {
        parent::__construct($model);
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
    }

    public function findOneByProviderTransactionId(string $providerTransactionId, int $whitelabelSlotProviderId): ?SlotTransaction
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('provider_transaction_id', $providerTransactionId),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $whitelabelSlotProviderId)
        ]);
        return $this->findOne();
    }

    /**
     * @param string[] $providerTransactionId
     * @return SlotTransaction[]
     */
    public function findAllByProviderTransactionIds(array $providerTransactionId, int $whitelabelSlotProviderId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('provider_transaction_id', $providerTransactionId, 'IN'),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $whitelabelSlotProviderId),
            new Model_Orm_Criteria_Where('is_canceled', false)
        ]);
        return $this->getResults();
    }

    /**
     * @param SlotTransaction $slotTransaction
     * @throws Exception
     */
    public function setTransactionAsCanceled(SlotTransaction $slotTransaction): void
    {
        $slotTransaction->isCanceled = true;
        $slotTransaction->canceledAt = new Carbon($slotTransaction->getTimezoneForField('isCanceled'));
        $slotTransaction->save();
    }

    /**
     * @param SlotTransaction[] $slotTransactions
     * @throws Exception
     */
    public function setTransactionsAsCanceled(array $slotTransactions): void
    {
        $transactionsIds = array_column($slotTransactions, 'id');
        $firstSlotTransaction = $slotTransactions[0];
        $canceledAt = new Carbon($firstSlotTransaction->getTimezoneForField('isCanceled'));

        $this->db->update(SlotTransaction::get_table_name())
            ->set([
                'is_canceled' => true,
                'canceled_at' => $canceledAt->format(Helpers_Time::DATETIME_FORMAT)
            ])
            ->where('id', 'IN', $transactionsIds)
            ->execute();
    }

    public function findGgrPerWhitelabel(Config $config, float $currencyUsdRate): array
    {
        $query = $this->db->selectArray([
            [$this->db->expr('MIN(whitelabel.name)'), 'whitelabel_name'],
            [$this->db->expr('count(*) OVER()'), 'fullCount'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'bet')), 'bets'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'win')), 'wins'],
            [$this->db->expr($this->getGGrQueryExpression($currencyUsdRate)), 'ggr'],
        ])
            ->from($this->db->expr('slot_transaction FORCE INDEX (is_canceled_created_at_action_amount_usd_wlsp_id_index)'))
            ->join('whitelabel_slot_provider', 'LEFT')
            ->on('whitelabel_slot_provider.id', '=', 'slot_transaction.whitelabel_slot_provider_id')
            ->join('whitelabel', 'LEFT')
            ->on('whitelabel.id', '=', 'whitelabel_slot_provider.whitelabel_id')
            ->where('slot_transaction.is_canceled', '=', $this->db->expr('0'))
            ->and_where('slot_transaction.created_at', 'BETWEEN', [$config->fromDatetime, $config->toDatetime]);

        $filters = array_map(function ($filter) {
            switch ($filter['column']) {
                case 'whitelabel_name':
                    $filter['column'] = 'whitelabel.name';
                    break;
            }
            return $filter;
        }, $config->filters);

        if ($config->isNotSuperadminView) {
            $query->and_where('whitelabel.id', '=', $config->whitelabelId);
            $this->addFiltersToQueryBuilderOnNonaggregatedData($filters, $query);
        } else {
            $query->group_by('whitelabel.id');
            $this->addFiltersToQueryBuilderOnAggregatedData($config->filters, $query);
        }

        $total = $this->getTotalFromGgrQuery($query);

        if (!empty($config->sortBy)) {
            $query->order_by($config->sortBy, $config->order);
        }

        /** @var mixed $result */
        $result = $query->offset($config->offset)
            ->limit($config->itemsPerPage)
            ->execute();

        /** @var array{array{whitelabel_name: string, bets: float, wins: float, ggr: float, fullCount: int}} $data */
        $data = $result->as_array();

        return [
            'data' => $data,
            'fullDataCount' => $data[0]['fullCount'] ?? 0,
            'total' => $total
        ];
    }

    /** @return array{data: array, total: array} */
    public function findGgrPerGame(Config $config, float $currencyUsdRate): array
    {
        $query = $this->db->selectArray([
            [$this->db->expr('MIN(slot_game.name)'), 'game_name'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'bet')), 'bets'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'win')), 'wins'],
            [$this->db->expr($this->getGGrQueryExpression($currencyUsdRate)), 'ggr']
        ]);

        $query->from(SlotTransaction::get_table_name())
            ->join('slot_game', 'LEFT')
            ->on('slot_game.id', '=', 'slot_transaction.slot_game_id');

        if ($config->isNotSuperadminView) {
            $query->join('whitelabel_slot_provider', 'LEFT')
                ->on('whitelabel_slot_provider.id', '=', 'slot_transaction.whitelabel_slot_provider_id')
                ->join('whitelabel', 'LEFT')
                ->on('whitelabel.id', '=', 'whitelabel_slot_provider.whitelabel_id')
                ->where('whitelabel.id', '=', $config->whitelabelId);
        }

        $query->and_where('slot_transaction.is_canceled', '=', $this->db->expr('0'))
            ->and_where('slot_transaction.created_at', 'BETWEEN', [$config->fromDatetime, $config->toDatetime]);

        $query->group_by('slot_game.id')
            ->having('bets', '>', '0');
        $this->addFiltersToQueryBuilderOnAggregatedData($config->filters, $query);

        //$total = $this->getTotalFromGgrQuery($query);
        $total = 0;

        if (!empty($config->sortBy)) {
            $query->order_by($config->sortBy, $config->order);
        }

        /** @var mixed $result */
        $result = $query->offset($config->offset)
            ->limit($config->itemsPerPage)
            ->execute();

        /** @var array{game_name: string, bets: float, wins: float, ggr: float} $data */
        $data = $result->as_array();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /** @return array{data: array, total: array} */
    public function findGgrPerGameProvider(Config $config, float $currencyUsdRate): array
    {
        $query = $this->db->selectArray([
            [$this->db->expr('MIN(slot_game.provider)'), 'game_provider'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'bet')), 'bets'],
            [$this->db->expr($this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'win')), 'wins'],
            [$this->db->expr($this->getGGrQueryExpression($currencyUsdRate)), 'ggr']
        ]);

        $query->from(SlotTransaction::get_table_name())
            ->join('slot_game', 'LEFT')
            ->on('slot_game.id', '=', 'slot_transaction.slot_game_id');

        if ($config->isNotSuperadminView) {
            $query->join('whitelabel_slot_provider', 'LEFT')
                ->on('whitelabel_slot_provider.id', '=', 'slot_transaction.whitelabel_slot_provider_id')
                ->join('whitelabel', 'LEFT')
                ->on('whitelabel.id', '=', 'whitelabel_slot_provider.whitelabel_id')
                ->where('whitelabel.id', '=', $config->whitelabelId);
        }

        $query->and_where('slot_transaction.is_canceled', '=', $this->db->expr('0'))
            ->and_where('slot_transaction.created_at', 'BETWEEN', [$config->fromDatetime, $config->toDatetime]);

        $query->group_by('slot_game.provider')
            ->having('bets', '>', '0');
        $this->addFiltersToQueryBuilderOnAggregatedData($config->filters, $query);

        //$total = $this->getTotalFromGgrQuery($query);
        $total = 0;

        if (!empty($config->sortBy)) {
            $query->order_by($config->sortBy, $config->order);
        }

        /** @var mixed $result */
        $result = $query->offset($config->offset)
            ->limit($config->itemsPerPage)
            ->execute();

        /** @var array{game_provider: string, bets: float, wins: float, ggr: float} $data */
        $data = $result->as_array();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /** @return array{bets: float, wins: float, ggr: float} */
    private function getTotalFromGgrQuery(Database_Query_Builder $query): array
    {
        /** @var mixed $result */
        $result = $this->db->selectArray([
            [$this->db->expr('COALESCE(SUM(bets), 0)'), 'bets'],
            [$this->db->expr('COALESCE(SUM(wins), 0)'), 'wins'],
            [$this->db->expr('COALESCE(SUM(ggr), 0)'), 'ggr'],
        ])
            ->from($this->db->expr("(" . $query->compile() . ") AS ggr"))
            ->execute();

        return $result->as_array();
    }

    public function sumInUsdWithBetActionForCurrentMonth(int $whitelabelId): float
    {
        $whitelabelSlotProvidersIds = $this->whitelabelSlotProviderRepository->findIdsByWhitelabelId($whitelabelId);

        $timezone = $this->model->getTimezoneForField('createdAt');
        $fromDatetime = Helpers_Crm_General::prepare_start_date(Helpers_Time::getFirstDateInCurrentMonth($timezone));
        $toDatetime = Helpers_Crm_General::prepare_end_date(Helpers_Time::getLastDateInCurrentMonth($timezone));

        /** @var object $result */
        $result = $this->db->select($this->db->expr('COALESCE(SUM(amount_usd), 0) AS sum'))
            ->from(SlotTransaction::get_table_name())
            ->where('whitelabel_slot_provider_id', 'IN', $whitelabelSlotProvidersIds)
            ->and_where('action', '=', SlotTransaction::ACTION_BET)
            ->and_where('created_at', 'BETWEEN', [$fromDatetime, $toDatetime])
            ->execute();

        $usdTransactionsAmount = $result->as_array();

        if (empty($usdTransactionsAmount)) {
            return 0;
        }

        return $usdTransactionsAmount[0]['sum'] ?? 0;
    }

    private function getSpecificSlotTransactionsQueryExpression(float $currencyUsdRate, string $type): string
    {
        return "ROUND(COALESCE(SUM((slot_transaction.action = '$type') * slot_transaction.amount_usd), 0) * $currencyUsdRate, 2)";
    }

    private function getGGrQueryExpression(float $currencyUsdRate): string
    {
        $bets = $this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'bet');
        $wins = $this->getSpecificSlotTransactionsQueryExpression($currencyUsdRate, 'win');
        return "$bets - $wins";
    }

    public function getUserSummaryForMediacle(int $whitelabelId, Carbon $date): array
    {
        $whitelabelSlotProvidersIds = $this->whitelabelSlotProviderRepository->findIdsByWhitelabelId($whitelabelId);
        $isWhitelabelWithoutCasino = empty($whitelabelSlotProvidersIds);
        if ($isWhitelabelWithoutCasino) {
            return [];
        }

        $formattedDate = $date->format(Helpers_Time::DATE_FORMAT);
        $whitelabelSlotProvidersIds = implode(', ', $whitelabelSlotProvidersIds);

        $where = " WHERE 
        whitelabel_slot_provider_id IN ($whitelabelSlotProvidersIds)
         AND DATE(slot_transaction.created_at) = '$formattedDate'
         ";

        /** @var object $result */
        $result = $this->db->select(
            $this->db->expr('CONCAT(whitelabel.prefix, "U", whitelabel_user.token) AS user_token'),
            'bets_summary.bets',
            'wins_summary.wins',
            $this->db->expr('(bets_summary.bets - wins_summary.wins) AS ggr'),
            $this->db->expr('(' . WhitelabelAffSlotCommissionRepository::AVERAGE_COMMISSION_OF_GAME_PROVIDERS . ' * (SELECT ggr)) AS costs'),
            $this->db->expr('(COALESCE((SELECT ggr) - (SELECT costs) - whitelabel_user.casino_bonus_balance)) AS revenue'),
            $this->db->expr('whitelabel.name AS whitelabel_name'),
            $this->db->expr('whitelabel_user.casino_bonus_balance AS casino_bonus_balance'),
            $this->db->expr('DATE(slot_transaction.created_at) AS created_at'),
            $this->db->expr('whitelabel_user.first_deposit AS first_deposit'),
            $this->db->expr('ANY_VALUE(whitelabel_user_aff.btag) AS btag'),
            $this->db->expr('ANY_VALUE(whitelabel_aff.token) AS tracking_id'),
        )
            ->from(SlotTransaction::get_table_name())
            ->join('whitelabel_user', 'INNER')
            ->on('slot_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel', 'INNER')
            ->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->join([$this->db->expr("
                SELECT whitelabel_user_id, COALESCE(SUM(amount_usd), 0) AS bets
                FROM slot_transaction
                $where
                AND action = 'bet'
                GROUP BY whitelabel_user_id
            "), 'bets_summary'], 'LEFT')
            ->on('bets_summary.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join([$this->db->expr("
                SELECT whitelabel_user_id, COALESCE(SUM(amount_usd), 0) AS wins
                FROM slot_transaction
                $where
                AND action = 'win'
                GROUP BY whitelabel_user_id
            "), 'wins_summary'], 'LEFT')
            ->on('wins_summary.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_user_aff', 'LEFT')
            ->on('whitelabel_user_aff.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_aff', 'LEFT')
            ->on('whitelabel_user_aff.whitelabel_aff_id', '=', 'whitelabel_aff.id')
            ->where($this->db->expr(str_replace('WHERE', '', $where)))
            ->and_where($this->db->expr('(bets_summary.bets - wins_summary.wins)'), '>', 0)
            ->group_by(
                'slot_transaction.whitelabel_user_id',
            $this->db->expr('DATE(slot_transaction.created_at)')
            )
            ->execute();

        $result = $result->as_array();

        return array_map(
            fn (array $transaction) => new SalesDataSlotTransactionModelAdapter($transaction),
            $result
        );
    }
}
