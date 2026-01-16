<?php

namespace Fuel\Tasks;

use Container;
use Repositories\WhitelabelApiNonceRepository;
use Repositories\WhitelabelLotteryPurchaseLimitRepository;
use Repositories\WhitelabelPluginLogRepository;
use Abstracts\Tasks\AbstractClearLogs;
use Helpers\CaseHelper;
use Repositories\LottorisqLogRepository;
use Repositories\Orm\PaymentLogRepository;
use Repositories\RaffleLogRepository;
use Repositories\LotteryLogRepository;
use Repositories\IpLoginTryRepository;
use Repositories\ImvalapLogRepository;
use Repositories\SlotLogRepository;

/**
 * To implement new log cleaner you have to:
 * 1. Fill constants DELETE_OLDER_THAN_X_DAYS and DELETE_OLDER_THAN_X_MINUTES
 * 2. Fill constant TABLES_WITH_CUSTOM_DATE_COLUMN if the column name is different than `date`
 * 3. You have to add new private variable + get the value with Container:: in __construct().
 * 4. Add new table to scheduler.php
 * - Zero means truncating the entire table
 */
final class Clear_Logs extends AbstractClearLogs
{
    private const DELETE_OLDER_THAN_X_DAYS = [
        'whitelabel_plugin_log' => 30,
        'raffle_log' => 7,
        'payment_log' => 365,
        'lottorisq_log' => 30,
        'lottery_log' => 7,
        'ip_login_try' => 10,
        'slot_log' => 30,
        'whitelabel_lottery_purchase_limit' => 0,
    ];

    private const DELETE_OLDER_THAN_X_MINUTES = [
        'whitelabel_api_nonce' => 30
    ];

    /**
     * This constant points out the tables that don't have date column different than `date`
     */
    private const TABLES_WITH_CUSTOM_DATE_COLUMN = [
        'ip_login_try' => 'last_login_try_at',
        'slot_log' => 'created_at',
        'whitelabel_lottery_purchase_limit' => 'created_at',
    ];

    private WhitelabelApiNonceRepository $whitelabelApiNonceRepository;
    private WhitelabelPluginLogRepository $whitelabelPluginLogRepository;
    private RaffleLogRepository $raffleLogRepository;
    private PaymentLogRepository $paymentLogRepository;
    private LottorisqLogRepository $lottorisqLogRepository;
    private LotteryLogRepository $lotteryLogRepository;
    private IpLoginTryRepository $ipLoginTryRepository;
    private ImvalapLogRepository $imvalapLogRepository;
    private SlotLogRepository $slotLogRepository;
    private WhitelabelLotteryPurchaseLimitRepository $whitelabelLotteryPurchaseLimitRepository;

    public function __construct()
    {
        $this->whitelabelApiNonceRepository = Container::get(WhitelabelApiNonceRepository::class);
        $this->whitelabelPluginLogRepository = Container::get(WhitelabelPluginLogRepository::class);
        $this->raffleLogRepository = Container::get(RaffleLogRepository::class);
        $this->paymentLogRepository = Container::get(PaymentLogRepository::class);
        $this->lottorisqLogRepository = Container::get(LottorisqLogRepository::class);
        $this->lotteryLogRepository = Container::get(LotteryLogRepository::class);
        $this->ipLoginTryRepository = Container::get(IpLoginTryRepository::class);
        $this->imvalapLogRepository = Container::get(ImvalapLogRepository::class);
        $this->slotLogRepository = Container::get(SlotLogRepository::class);
        $this->whitelabelLotteryPurchaseLimitRepository = Container::get(WhitelabelLotteryPurchaseLimitRepository::class);
    }

    /**
     * Running this task without any param clears all implemented tables
     * based on days/minutes provided in constants
     */
    public function run(string $tableName = 'all'): void
    {
        if ($tableName === 'all') {
            $this->clearAll();
            return;
        }

        $isTableNotImplemented = !in_array($tableName, array_keys(self::DELETE_OLDER_THAN_X_MINUTES))
            && !in_array($tableName, array_keys(self::DELETE_OLDER_THAN_X_DAYS));

        if ($isTableNotImplemented) {
            echo "Table $tableName is not implemented in task clear_logs.php \n";
            return;
        }

        list($deleteTime, $method, $repository, $dateColumn) = $this->getClearDetails($tableName);
        $this->$method($this->$repository, $deleteTime, $dateColumn);
    }

    private function clearAll(): void
    {
        $allTables = array_merge(self::DELETE_OLDER_THAN_X_DAYS, self::DELETE_OLDER_THAN_X_MINUTES);
        $allTables = array_keys($allTables);

        foreach ($allTables as $tableName) {
            list($deleteTime, $method, $repository, $dateColumn) = $this->getClearDetails($tableName);
            $this->$method($this->$repository, $deleteTime, $dateColumn);
        }
    }

    private function getClearDetails(string $tableName): array
    {
        $method = isset(self::DELETE_OLDER_THAN_X_MINUTES[$tableName]) ? 'clearLogsByMinutes' : 'clearLogsByDays';
        $isClearByMinutesMethod = $method === 'clearLogsByMinutes';
        $dateColumn = isset(self::TABLES_WITH_CUSTOM_DATE_COLUMN[$tableName]) ? self::TABLES_WITH_CUSTOM_DATE_COLUMN[$tableName] : 'date';
        $repositoryInCamelCase = CaseHelper::snakeToCamel($tableName) . 'Repository';
        $deleteTime = $isClearByMinutesMethod ? self::DELETE_OLDER_THAN_X_MINUTES[$tableName] : self::DELETE_OLDER_THAN_X_DAYS[$tableName];

        return [
            $deleteTime,
            $method,
            $repositoryInCamelCase,
            $dateColumn
        ];
    }
}
