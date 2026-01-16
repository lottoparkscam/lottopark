<?php

namespace Repositories\Orm;

use Carbon\Carbon;
use DateTimeInterface;
use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder_Where;
use Fuel\Core\Database_Result;
use Helpers_General;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Expr;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Modules\Mediacle\Models\SalesDataWhitelabelTransactionModelAdapter;
use Modules\Mediacle\Repositories\SalesDataByDateContract;
use Orm\RecordNotFound;
use Wrappers\Db;
use Throwable;
use Fuel\Core\Date;
use Services\Logs\FileLoggerService;

class TransactionRepository extends AbstractRepository implements SalesDataByDateContract
{
    /**
     *  Not elegant way to limit transactions only to new payments approach..
     *  Id = 30 is Jeton.
     */
    public const FIRST_NEW_PAYMENT_ID = 30;
    protected Db $db;
    private FileLoggerService $fileLoggerService;

    public function __construct(WhitelabelTransaction $model, Db $db, FileLoggerService $fileLoggerService)
    {
        parent::__construct($model);
        $this->model = $model;
        $this->db = $db;
        $this->fileLoggerService = $fileLoggerService;
    }

    /**
     * Searches transaction by token and whitelabel id. Tokens are unique per whitelabel, but not unique per entire system.
     * @throws RecordNotFound
     */
    public function getByToken(string $token, int $whitelabelId, array $relations = []): WhitelabelTransaction
    {
        WhitelabelTransaction::flush_cache();
        $this->pushRelations($relations);

        # here we have a token with prefix, our db expects int values
        # so we have to convert it..
        $token = $this->getRawTokenId($token);

        $this->model->push_criterias(
            [
                new Model_Orm_Criteria_Where('token', $token),
                new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            ]
        );

        /** @var ?WhitelabelTransaction $t */
        $t = $this->model->find_one();

        if (empty($t)) {
            throw new RecordNotFound("Unable to find transaction with token #$token and whitelabel id #$whitelabelId");
        }

        return $t;
    }

    /**
     * Searches transaction by token and whitelabel prefix extracted from token. Tokens are unique per whitelabel, but not unique per entire system.
     * @throws RecordNotFound
     */
    public function getByPrefixedToken(string $prefixedToken): WhitelabelTransaction
    {
        $rawTokenId = $this->getRawTokenId($prefixedToken);
        $whitelabelPrefix = substr($prefixedToken, 0, 2);

        $this->model->push_criterias(
            [
                new Model_Orm_Criteria_Where('token', $rawTokenId),
                new Model_Orm_Criteria_With_Relation('whitelabel'),
                new Model_Orm_Criteria_Where('whitelabel.prefix', $whitelabelPrefix),
            ]
        );

        /** @var ?WhitelabelTransaction $transaction */
        $transaction = $this->model->find_one();

        if (empty($transaction)) {
            throw new RecordNotFound("Unable to find transaction with token #$rawTokenId and whitelabel prefix $whitelabelPrefix");
        }

        return $transaction;
    }

    public function getByTransactionOutId(
        int $whitelabelPaymentMethodId,
        string $outId
    ): WhitelabelTransaction {
        $this->model->push_criterias(
            [
                new Model_Orm_Criteria_Where('whitelabel_payment_method_id', $whitelabelPaymentMethodId),
                new Model_Orm_Criteria_Where('transaction_out_id', $outId),
            ]
        );

        $transaction = $this->model->find_one();

        if (empty($transaction)) {
            throw new RecordNotFound(sprintf(
                'Unable to find transaction with out ID %s and whitelabel payment method ID %s',
                $outId,
                $whitelabelPaymentMethodId
            ));
        }

        return $transaction;
    }

    public function save(AbstractOrmModel $transaction, bool $flush = true): bool
    {
        return $transaction->save();
    }

    private function getRawTokenId(string $prefixedToken): string
    {
        return preg_replace('/[^0-9]/', '', $prefixedToken); // @see platform/fuel/app/classes/validators/rules/PrefixedToken.php
    }

    public function getPendingTransactionIdsOlderThanXDays(int $days): array
    {
        $getOlderThanDate = Carbon::now()->subDays($days)->format('Y-m-d H:i');

        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('date', $getOlderThanDate, '<='),
            new Model_Orm_Criteria_Where('status', Helpers_General::STATUS_TRANSACTION_PENDING),
            new Model_Orm_Criteria_Where('date_confirmed', null, 'IS')
        ]);

        return $this->getResultsForSingleField() ?? [];
    }

    /**
     * We need:
     * - limited amount of transactions
     * - transaction with pending status
     * - not younger than 15 minutes (let's give a chance for instant payment notification)
     * - not older than given value
     *
     * - ordered by attempt_date ASC
     * @return WhitelabelTransaction[]
     */
    public function getPendingTransaction(
        DateTimeInterface $dateFrom,
        DateTimeInterface $dateTo,
        DateTimeInterface $lastAttemptDate,
        int $maxAttemptsCount,
        int $limit
    ): array {
        $dateFrom = $dateFrom->format('Y-m-d H:i');
        $dateTo = $dateTo->format('Y-m-d H:i');
        $lastAttemptDate = $lastAttemptDate->format('Y-m-d H:i');

        $selectFields = [
            'whitelabel_id',
            'whitelabel_payment_method_id',
            'payment_method_type',
            'date',
            'payment_attempts_count',
            'payment_attempt_date',
            'token',
            'status',
            'additional_data',
            'additional_data_json'
        ];

        return $this->model->push_criterias([
            new Model_Orm_Criteria_Select($selectFields),
            new Model_Orm_Criteria_Where('status', Helpers_General::STATUS_TRANSACTION_PENDING),
            new Model_Orm_Criteria_Where('payment_attempts_count', $maxAttemptsCount, '<'),
            new Model_Orm_Criteria_Where('whitelabel_payment_method.payment_method_id', self::FIRST_NEW_PAYMENT_ID, '>='),
            new Model_Orm_Criteria_Where('date', [$dateFrom, $dateTo], 'BETWEEN'),
            new Model_Orm_Criteria_Expr(
                $this->db->expr("(payment_attempt_date IS NULL OR payment_attempt_date <= '$lastAttemptDate')")
            ),
            new Model_Orm_Criteria_With_Relation('whitelabel_payment_method')
        ])
            ->order_by('payment_attempt_date', 'ASC')
            ->get_results($limit);
    }

    /** @return WhitelabelTransaction[] */
    public function getOutDatedTransactions(DateTimeInterface $date, int $limit = 1000): array
    {
        $date = $date->format('Y-m-d H:i');

        return $this->model->push_criterias([
            new Model_Orm_Criteria_Where('status', Helpers_General::STATUS_TRANSACTION_PENDING),
            new Model_Orm_Criteria_Where('date', $date, "<="),
        ])
            ->order_by('date', 'ASC')
            ->get_results($limit);
    }

    private function pushRelations(array $relations = []): void
    {
        foreach ($relations as $relation) {
            $this->model->push_criteria(
                new Model_Orm_Criteria_With_Relation($relation)
            );
        }
    }

    /**
     * This function uses query builder in order to improve performance.
     * Saving this with save() method requires nearly the whole WhitelabelTransaction object.
     * With save() we have to use select * to modify two fields.
     * This function is used only in synchronizer.
     */
    public function attemptPayment(string $prefixedToken, int $whitelabelId): void
    {
        $token = $this->getRawTokenId($prefixedToken);
        $attemptDate = Date::forge()->format('mysql');

        $values = [
            'payment_attempt_date' => $attemptDate,
            'payment_attempts_count' => $this->db->expr('payment_attempts_count + 1')
        ];

        try {
            $this->db->update($this->model->get_table_name())
                ->set($values)
                ->where('token', '=', $token)
                ->and_where('whitelabel_id', '=', $whitelabelId)
                ->execute();
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                "Error during synchronizer attemptPayment(): " . $e->getMessage()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function findSalesDataByDate(int $whitelabelId, Carbon $date): array
    {
        $dateFrom = $date->format('Y-m-d') . ' 00:00:00';
        $dateTo = $date->format('Y-m-d') . ' 23:59:59';

        $this->pushCriteria(new Model_Orm_Criteria_Where('date', [$dateFrom, $dateTo], 'BETWEEN'));

        $this->pushCriteria(new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId));

        $this->pushCriteria(new Model_Orm_Criteria_Where('status', Helpers_General::STATUS_TRANSACTION_APPROVED));
        $this->pushCriteria(new Model_Orm_Criteria_Where('is_casino', false));

        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel_tickets'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('whitelabel_raffle_ticket'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('user.whitelabel_user_aff.whitelabel_aff'));
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation('user.whitelabel_user_promo_code.whitelabel_promo_code.whitelabel_campaign'));

        $this->orderBy('date', 'ASC');
        $this->orderBy('id', 'ASC');

        $results = $this->getResults();
        return array_map(
            fn (WhitelabelTransaction $transaction) => new SalesDataWhitelabelTransactionModelAdapter($transaction),
            $results
        );
    }

    // We store casino deposits in whitelabel_transaction table
    public function findCasinoDepositsDataByDate(int $whitelabelId, Carbon $date): array
    {
        $dateFrom = $date->format('Y-m-d') . ' 00:00:00';
        $dateTo = $date->format('Y-m-d') . ' 23:59:59';

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('date', [$dateFrom, $dateTo], 'BETWEEN'),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('status', Helpers_General::STATUS_TRANSACTION_APPROVED),
            new Model_Orm_Criteria_Where('is_casino', true),
            new Model_Orm_Criteria_Where('type', WhitelabelTransaction::TYPE_DEPOSIT),
            new Model_Orm_Criteria_With_Relation('whitelabel'),
            new Model_Orm_Criteria_With_Relation('user.whitelabel_user_aff.whitelabel_aff'),
            new Model_Orm_Criteria_With_Relation('user.whitelabel_user_promo_code.whitelabel_promo_code.whitelabel_campaign')
        ]);

        $this->orderBy('date', 'ASC');
        $this->orderBy('id', 'ASC');

        $results = $this->getResults();
        return array_map(
            fn (WhitelabelTransaction $transaction) => new SalesDataWhitelabelTransactionModelAdapter($transaction),
            $results
        );
    }

    /**
     * @param array{startDate: string, endDate: string} $dates
     */
    public function getPaymentCostForDepositOnly(array $dates, ?int $languageId, ?string $country, ?int $whitelabelId, ?int $whitelabelType, ?bool $isFullReport): array
    {
        $userTableName = WhitelabelUser::get_table_name();
        $whitelabelTableName = Whitelabel::get_table_name();
        $transactionTableName = $this->model::get_table_name();

        $sumExpression = $this->db->expr('SUM(payment_cost_manager) AS payment_cost_manager, SUM(payment_cost_usd) AS payment_cost_usd');

        $query = $this->db->select($sumExpression)
            ->from($transactionTableName)
            ->join($userTableName)
            ->on($userTableName . '.id', '=', $transactionTableName . '.whitelabel_user_id')
            ->join($whitelabelTableName)
            ->on($whitelabelTableName . '.id', '=', $transactionTableName . '.whitelabel_id')
            ->where($transactionTableName . '.type', Helpers_General::TYPE_TRANSACTION_DEPOSIT)
            ->where($transactionTableName . '.status', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->where($transactionTableName . '.date', 'BETWEEN', $dates);

        /** @var Database_Query_Builder_Where $query */
        if ($whitelabelId) {
            $query->where($transactionTableName . '.whitelabel_id', $whitelabelId);
        }

        if ($whitelabelType) {
            $query->where($whitelabelTableName . '.type', $whitelabelType);
        }
        if ($languageId) {
            $query->where($userTableName . '.language_id', $languageId);
        }

        if ($country) {
            $query->where($userTableName . '.country', $country);
        }

        if ($isFullReport) {
            $query->where($whitelabelTableName . '.is_report', 1);
            /** @var Database_Query $query */
        }
        /** @var Database_Result */
        $paymentCosts = $query->execute();
        $paymentCosts->as_array();

        return [
            'paymentCostManager' => (float)($paymentCosts[0]['payment_cost_manager'] ?? 0),
            'paymentCostUsd' => (float)($paymentCosts[0]['payment_cost_usd'] ?? 0),
        ];
    }
}
