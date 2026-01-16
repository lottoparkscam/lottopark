<?php

namespace Repositories\Orm;

use Carbon\Carbon;
use Exception;
use Helpers_Currency;
use Classes\Orm\Criteria\Model_Orm_Criteria_Today;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelUser;
use Models\WhitelabelUserBalanceLog;

class WhitelabelUserBalanceLogRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserBalanceLog $model)
    {
        parent::__construct($model);
    }

    /**
     * BalanceChange field is signed. Negative if balance was decreased and positive if increased.
     * @param int $whitelabelUserId
     * @param string $message
     * @param float $balanceChange
     * @param string $balanceChangeCurrencyCode
     * @param float $balanceChangeBeforeConversion
     * @param string|null $balanceChangeBeforeConversionCurrencyCode
     * @param float $balanceChangeImport
     * @param string|null $balanceChangeImportCurrencyCode
     * @param bool $isBonus
     * @param int $level
     * @return bool
     * @throws Exception
     */
    public function addWhitelabelUserBalanceLog(
        int $whitelabelUserId,
        string $message,
        float $balanceChange,
        string $balanceChangeCurrencyCode,
        float $balanceChangeBeforeConversion = 0,
        string $balanceChangeBeforeConversionCurrencyCode = null,
        float $balanceChangeImport = 0,
        string $balanceChangeImportCurrencyCode = null,
        bool $isBonus = false,
        int $level = 0
    ): bool {
        $sessionDatetime = Carbon::now();

        $log = new $this->model();
        $log->set([
            'whitelabel_user_id' => $whitelabelUserId,
            'created_at' => $this->db->expr("NOW()"),
            'session_datetime' => $sessionDatetime,
            'message' => $message,
            'level' => $level,
            'is_bonus' => $isBonus,
            'balance_change' => $balanceChange,
            'balance_change_currency_code' => $balanceChangeCurrencyCode,
            'balance_change_import' => $balanceChangeImport,
            'balance_change_import_currency_code' => $balanceChangeImportCurrencyCode,
            'balance_change_before_conversion' => $balanceChangeBeforeConversion,
            'balance_change_before_conversion_currency_code' => $balanceChangeBeforeConversionCurrencyCode
        ]);
        return $log->save();
    }

    public function calculateChangedBonusBalancePerUser(WhitelabelUser $user): float
    {
        $whitelabel = $user->whitelabel;
        $increaseByNegativeValues = $whitelabel->isReducingBalanceIncreasesLimits;

        $historyRows = $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $user->id),
            new Model_Orm_Criteria_Today('session_datetime')
        ])->getResults();

        $balanceSum = 0.00;

        /** @var WhitelabelUserBalanceLog $row */
        foreach ($historyRows as $row) {
            if (!$increaseByNegativeValues && $row->balance_change < 0) {
                continue;
            }

            $balanceChangeInWhitelabelCurrency = (float)Helpers_Currency::convert_to_any(
                $row->balance_change,
                $row->balance_change_currency_code,
                $whitelabel->currency->code
            );

            $balanceSum += $balanceChangeInWhitelabelCurrency;
        }

        return $balanceSum;
    }
}
