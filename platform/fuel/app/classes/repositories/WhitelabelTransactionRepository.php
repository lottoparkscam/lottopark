<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Container;
use Exception;
use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Helpers_Crm_General;
use Helpers_General;
use Models\WhitelabelTransaction;
use Modules\CrmTable\Config;
use Repositories\Orm\AbstractRepository;
use Services\Logs\FileLoggerService;

/**
 * @method WhitelabelTransaction findOneById(int $id)
 * @method WhitelabelTransaction findOneByToken(string $token)
*/
class WhitelabelTransactionRepository extends AbstractRepository
{
    public function __construct(WhitelabelTransaction $whitelabelTransaction)
    {
        parent::__construct($whitelabelTransaction);
    }

    public function getTransactionGroupedPerMethod(
        bool $isCasino,
        bool $isDeposit,
        string $startDate,
        string $endDate,
        int $whitelabelId,
        Config $crmTableConfig,
    ): array
    {
        $baseQuery = $this->getTransactionGroupedPerMethodQuery(
            $isCasino,
            $isDeposit,
            $startDate,
            $endDate,
            $whitelabelId,
        );

        $query = $this->db->select('*')->from([$baseQuery, 'report']);
        $this->addFiltersToQueryBuilderOnAggregatedData($crmTableConfig->filters, $query);

        $query->limit($crmTableConfig->itemsPerPage)
            ->offset($crmTableConfig->offset);

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array();
    }

    public function getTransactionNumbersGroupedPerMethod(
        bool $isCasino,
        bool $isDeposit,
        string $startDate,
        string $endDate,
        int $whitelabelId,
        Config $crmTableConfig,
    ): int
    {
        $baseQuery = $this->getTransactionNumbersGroupedPerMethodQuery(
            $isCasino,
            $isDeposit,
            $startDate,
            $endDate,
            $whitelabelId,
        );

        $query = $this->db->select('*')->from([$baseQuery, 'report']);
        $this->addFiltersToQueryBuilderOnAggregatedData($crmTableConfig->filters, $query);

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return count($result->as_array());
    }

    public function getTransactionGroupedPerMethodQuery(
        bool $isCasino,
        bool $isDeposit,
        string $startDate,
        string $endDate,
        int $whitelabelId,
    ): Database_Query
    {
        $isCasino = $isCasino ? 1 : 0;
        $whitelabelExists = $whitelabelId > 0;
        $whereWhitelabelId = $whitelabelExists ? "AND wt.whitelabel_id = $whitelabelId" : '';
        $amountToSum = $whitelabelExists ? 'wt.amount_manager' : 'wt.amount_usd';
        $type = $isDeposit ? Helpers_General::TYPE_TRANSACTION_DEPOSIT : Helpers_General::TYPE_TRANSACTION_PURCHASE;
        return DB::query("SELECT 
            payment_method.name as method_name,
            SUM(CASE WHEN wt.status = 0 THEN 1 ELSE 0 END) / count(*) * 100 AS pending_rate,
            SUM(CASE WHEN wt.status = 0 THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN wt.status = 0 THEN $amountToSum ELSE 0 END) AS pending_amount,
            SUM(CASE WHEN wt.status = 1 THEN 1 ELSE 0 END) / count(*) * 100 AS approved_rate,
            SUM(CASE WHEN wt.status = 1 THEN 1 ELSE 0 END) AS approved_count,
            SUM(CASE WHEN wt.status = 1 THEN $amountToSum ELSE 0 END) AS approved_amount,
            SUM(CASE WHEN wt.status = 2 THEN 1 ELSE 0 END) / count(*) * 100 AS error_rate,
            SUM(CASE WHEN wt.status = 2 THEN 1 ELSE 0 END) AS error_count,
            SUM(CASE WHEN wt.status = 2 THEN $amountToSum ELSE 0 END) AS error_amount
        FROM whitelabel_transaction as wt 
        LEFT JOIN whitelabel_payment_method ON wt.whitelabel_payment_method_id = whitelabel_payment_method.id 
        LEFT JOIN payment_method ON whitelabel_payment_method.payment_method_id = payment_method.id
        WHERE wt.is_casino = $isCasino
        AND wt.date >= '$startDate' AND wt.date <= '$endDate'
        AND wt.type = $type
        $whereWhitelabelId GROUP BY method_name
        ");
    }

    public function getTransactionNumbersGroupedPerMethodQuery(
        bool $isCasino,
        bool $isDeposit,
        string $startDate,
        string $endDate,
        int $whitelabelId,
    ): Database_Query
    {
        $isCasino = $isCasino ? 1 : 0;
        $whitelabelExists = $whitelabelId > 0;
        $whereWhitelabelId = $whitelabelExists ? "AND wt.whitelabel_id = $whitelabelId" : '';
        $amountToSum = $whitelabelExists ? 'wt.amount_manager' : 'wt.amount_usd';
        $type = $isDeposit ? Helpers_General::TYPE_TRANSACTION_DEPOSIT : Helpers_General::TYPE_TRANSACTION_PURCHASE;
        return DB::query("SELECT payment_method.name as method_name,
            SUM(CASE WHEN wt.status = 0 THEN 1 ELSE 0 END) / count(*) * 100 AS pending_rate,
            SUM(CASE WHEN wt.status = 0 THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN wt.status = 0 THEN $amountToSum ELSE 0 END) AS pending_amount,
            SUM(CASE WHEN wt.status = 1 THEN 1 ELSE 0 END) / count(*) * 100 AS approved_rate,
            SUM(CASE WHEN wt.status = 1 THEN 1 ELSE 0 END) AS approved_count,
            SUM(CASE WHEN wt.status = 1 THEN $amountToSum ELSE 0 END) AS approved_amount,
            SUM(CASE WHEN wt.status = 2 THEN 1 ELSE 0 END) / count(*) * 100 AS error_rate,
            SUM(CASE WHEN wt.status = 2 THEN 1 ELSE 0 END) AS error_count,
            SUM(CASE WHEN wt.status = 2 THEN $amountToSum ELSE 0 END) AS error_amount
        FROM whitelabel_transaction as wt
        LEFT JOIN whitelabel_payment_method ON wt.whitelabel_payment_method_id = whitelabel_payment_method.id 
        LEFT JOIN payment_method ON whitelabel_payment_method.payment_method_id = payment_method.id
        WHERE wt.is_casino = $isCasino 
        AND wt.date >= '$startDate' AND wt.date <= '$endDate'
        AND wt.type = $type
        $whereWhitelabelId GROUP BY method_name
        ");
    }

    public function getTransactionDetailsForCrmByTokenAndWhitelabelId(int $token, int $whitelabelId): array
    {
        $query = $this->db->select(
            'whitelabel_transaction.*',
            ['whitelabel.prefix','whitelabel_prefix'],
            ['currency.code','whitelabel_currency_code'],
            ['whitelabel_user.token', 'user_token'],
            ['whitelabel_user.name', 'user_name'],
            ['whitelabel_user.surname', 'user_surname'],
            ['whitelabel_user.email', 'user_email'],
            ['whitelabel_user.login', 'user_login'],
            ['whitelabel_payment_method.name', 'method']
        )
            ->from('whitelabel_transaction')
            ->join('whitelabel')->on('whitelabel_transaction.whitelabel_id', '=', 'whitelabel.id')
            ->join('currency', 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'currency.id')
            ->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->where('whitelabel_transaction.token', '=', $token)
            ->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);

        /** @var Database_Result $result */
        $result = $query->execute();
        return $result->as_array()[0];
    }

    public function getTransactionByTokenAndWhitelabelId(string $token, int $whitelabelId): array
    {
        $query = $this->db->selectArray([
            'whitelabel_payment_method_id',
            'amount_payment',
        ])
            ->from($this->model::get_table_name())
            ->where('token', $token)
            ->where('whitelabel_id', $whitelabelId);

        /** @var Database_Query_Builder $result */
        $result = $query->execute();
        return $result->as_array()[0] ?? [];
    }

    public function getCasinoDepositUsdSumforUsersForMautic(int $whitelabelId): array
    {
        $query = "
            SELECT
                whitelabel_user_id,
                SUM(CASE WHEN date_confirmed >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN amount_usd ELSE 0 END) AS last_30_days_casino_deposit,
                SUM(amount_usd) as total_casino_deposit
            FROM whitelabel_transaction
            WHERE
                type = 1
                AND is_casino = 1";
        
        if (!empty($whitelabelId)) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }

        $query .= " GROUP BY whitelabel_user_id";
        $query .= " HAVING total_casino_deposit > 0;";

        try {
            $db = DB::query($query);

            if (!empty($whitelabelId)) {
                $db->param(":whitelabel_id", $whitelabelId);
            }

            /** @var Database_Query_Builder $results */
            $results = $db->execute();

            return $results->as_array();
        } catch (Exception $e) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return [];
    }

    public function getPaymentGatewayPurchaseAndDepositSumForCrmDateRange(?int $whitelabelId, string $startDate, string $endDate): float
    {
        $sum = 0;

        $query = DB::select(DB::expr('SUM(amount_manager) as sum'))->from('whitelabel_transaction')
            ->where('type', 'IN', [Helpers_General::TYPE_TRANSACTION_PURCHASE, Helpers_General::TYPE_TRANSACTION_DEPOSIT])
            ->and_where_open()
                ->where('payment_method_type', '=', Helpers_General::PAYMENT_TYPE_OTHER)
                ->or_where(DB::expr('payment_method_type IS NULL'))
            ->and_where_close()
            ->and_where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('date_confirmed', '>=', Helpers_Crm_General::prepare_start_date($startDate))
            ->and_where('date_confirmed', '<=', Helpers_Crm_General::prepare_end_date($endDate));

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $result = $query->execute();
        if (!empty($result[0]['sum'])) {
            $sum = $result[0]['sum'];
        }

        return $sum;
    }

    public function getGatewayPurchaseAndDepositSumForCrmDateRangeByGateway(?int $whitelabelId, string $startDate, string $endDate): array
    {
        $query = DB::select(
            DB::expr('SUM(wt.amount_manager) as amount'),
            DB::expr('wpm.name as name')
        )
            ->from(['whitelabel_transaction', 'wt'])
            ->join(['whitelabel_payment_method', 'wpm'], 'LEFT')
            ->on('wt.whitelabel_payment_method_id', '=', 'wpm.id')
            ->where('wt.type', 'IN', [Helpers_General::TYPE_TRANSACTION_PURCHASE, Helpers_General::TYPE_TRANSACTION_DEPOSIT])
            ->and_where('wt.status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('wt.payment_method_type', '=', Helpers_General::PAYMENT_TYPE_OTHER)
            ->and_where('wt.date_confirmed', '>=', Helpers_Crm_General::prepare_start_date($startDate))
            ->and_where('wt.date_confirmed', '<=', Helpers_Crm_General::prepare_end_date($endDate))
            ->group_by('wpm.name');

        if ($whitelabelId) {
            $query->and_where('wt.whitelabel_id', '=', $whitelabelId);
        }

        /** @var Database_Query_Builder $results */
        $results = $query->execute();

        return $results->as_array() ?? [];
    }
}
