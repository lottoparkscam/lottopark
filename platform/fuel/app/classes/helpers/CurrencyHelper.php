<?php

namespace Helpers;

use Container;
use Exception;
use Helpers_Currency;
use Models\Currency;
use Repositories\Orm\CurrencyRepository;

class CurrencyHelper
{
    public static function getCurrentCurrency(): Currency
    {
        // Only lifetime cache
        global $currentCurrency;
        if (!empty($currentCurrency)) {
            return $currentCurrency;
        }

        $user = UserHelper::getUser();
        $isUser = !empty($user);
        if ($isUser) {
            $currentCurrency = $user->currency;
            return $user->currency;
        }

        $currencyRepository = Container::get(CurrencyRepository::class);
        $finalCurrencyCode = Helpers_Currency::get_final_currency_code();
        $defaultCurrency = $currencyRepository->findOneByCode($finalCurrencyCode);
        $currentCurrency = $defaultCurrency;
        return $defaultCurrency;
    }

    /**
     * @throws Exception
     */
    public static function getCurrencyByCode(string $currencyCode): Currency
    {
        $currencyRepository = Container::get(CurrencyRepository::class);

        $currency = $currencyRepository->findOneByCode($currencyCode);

        if ($currency === null) {
            throw new Exception(sprintf('Currency "%s" does not exist.', $currencyCode));
        }

        return $currency;
    }
}
