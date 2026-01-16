<?php

namespace Fuel\Tasks\Seeders;

use Container;
use Exception;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelPaymentMethod;
use Modules\Payments\PaymentFacadeLocator;
use Repositories\Orm\WhitelabelPaymentMethodRepository;

/**
 * Adds prefix to all payments config fields.
 */
final class Payments_Config_Fix extends Seeder
{
    private const FIRST_NEW_APPROACH_PAYMENT_ID = 30; # jeton id

    public function execute(): void
    {
        try {
            $repo = Container::get(WhitelabelPaymentMethodRepository::class);
            /** @var WhitelabelPaymentMethod[] $methods */
            $methods = $repo->pushCriteria(
                new Model_Orm_Criteria_Where('payment_method_id', self::FIRST_NEW_APPROACH_PAYMENT_ID, '>=')
            )->getResults();

            foreach ($methods as $method) {
                $paymentMethodId = $method->payment_method_id;
                if ($isNotNewApproach = $paymentMethodId < self::FIRST_NEW_APPROACH_PAYMENT_ID) {
                    continue;
                }
                $facade = Container::get(PaymentFacadeLocator::class)->getById($paymentMethodId);
                $facadeConfig = $facade->getConfig();
                $slug = $facadeConfig['slug'];
                $customizableOptions = $facade->getCustomizableOptions();

                $dataJson = $method->data_json;

                foreach ($customizableOptions as $prefixedKey) {
                    $notPrefixedKey = str_replace("{$slug}_", '', $prefixedKey);
                    if (isset($dataJson[$notPrefixedKey])) {
                        $newValue = $dataJson[$notPrefixedKey];
                        unset($dataJson[$notPrefixedKey]);
                        $dataJson[$prefixedKey] = $newValue;
                    }
                }

                $method->data_json = $dataJson;
                $repo->save($method);
            }
        } catch (Exception $e) {
            throw $e;
        }
        parent::execute();
    }

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        return [];
    }
}
