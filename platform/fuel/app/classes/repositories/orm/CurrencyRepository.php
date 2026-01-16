<?php

namespace Repositories\Orm;

use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Models\Currency;

/**
 * @method findOneByCode(string $finalCurrencyCode)
 */
class CurrencyRepository extends AbstractRepository
{
    public const ALL_CURRENCY_CODES_CACHE_KEY = 'all_currency_codes';

    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function getAllCodes(): array
    {
        try {
            $currencyCodes = Cache::get(self::ALL_CURRENCY_CODES_CACHE_KEY);
        } catch (CacheNotFoundException $exception) {
            $currencyCodes = $this->pushCriteria(
                new Model_Orm_Criteria_Select(['code'])
            )->getResultsForSingleField();
            Cache::set(self::ALL_CURRENCY_CODES_CACHE_KEY, $currencyCodes, Helpers_Time::HOUR_IN_SECONDS);
        }

        if (empty($currencyCodes)) {
            return [];
        }

        return $currencyCodes;
    }
}
