<?php

namespace Repositories\Orm;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelPaymentMethodCurrency;

class WhitelabelPaymentMethodCurrencyRepository extends AbstractRepository
{
    public function __construct(WhitelabelPaymentMethodCurrency $model)
    {
        parent::__construct($model);
    }

    public function getAllEnabledCurrencies(array $whitelabelPaymentMethodIds = []): array
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where('is_enabled', true));

        if (!empty($whitelabelPaymentMethodIds)) {
            $this->pushCriteria(
                new Model_Orm_Criteria_Where('whitelabel_payment_method_id', $whitelabelPaymentMethodIds, 'IN'),
            );
        }

        return $this->getResults();
    }

    public function getDefaultCurrencyForWhitelabelPaymentMethod(int $whitelabelPaymentMethodId): WhitelabelPaymentMethodCurrency
    {
        /** @var WhitelabelPaymentMethodCurrency $paymentMethodCurrency */
        $paymentMethodCurrency = $this->pushCriterias([
                                 new Model_Orm_Criteria_Where('whitelabel_payment_method_id', $whitelabelPaymentMethodId),
                                 new Model_Orm_Criteria_Where('is_default', true)
                             ])->getOne();
        return $paymentMethodCurrency;
    }

    /**
     * Used to check if user selected non-default currency
     */
    public function isCurrencySupportedForWhitelabelPaymentMethod(int $whitelabelPaymentMethodId, int $currencyId): bool
    {
        return $this->recordExists(
            [
                 new Model_Orm_Criteria_Where('whitelabel_payment_method_id', $whitelabelPaymentMethodId),
                 new Model_Orm_Criteria_Where('currency_id', $currencyId),
                 new Model_Orm_Criteria_Where('is_enabled', true)
            ]
        );
    }
}
