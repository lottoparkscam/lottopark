<?php

declare(strict_types=1);

namespace Repositories;

use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Database_Result;
use Helpers_Time;
use Models\WhitelabelPaymentMethod;
use Repositories\Orm\AbstractRepository;

class WhitelabelPaymentMethodRepository extends AbstractRepository
{
    public function __construct(WhitelabelPaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getSettingsByMethodIdAndWhitelabelId(int $methodId, int $whitelabelId): array
    {
        $query = $this->db->select('data')
            ->from($this->model::get_table_name())
            ->where('id', $methodId)
            ->where('whitelabel_id', $whitelabelId);

        /** @var Database_Result $result */
        $result = $query->execute();

        return unserialize($result->as_array()[0]['data']) ?? [];
    }

    public function getAllWhitelabelPaymentMethodIdsByMethodId(int $paymentMethodId): array
    {
        /** @var mixed $query */
        $query = $this->db->select('id')
            ->from($this->model::get_table_name())
            ->where('payment_method_id', $paymentMethodId);

        /** @var Database_Result $result */
        $result = $query->execute()->as_array();

        return array_column($result, 'id');
    }

    public function getPaymentApiSettingsMethodIdAndWhitelabelId(int $paymentMethodId, int $whitelabelId): array
    {
        $cacheKey = 'getSettingsDataByPaymentMethodIdAndWhitelabelId_' . $paymentMethodId . '_' . $whitelabelId;

        try {
            $paymentApiSettings = Cache::get($cacheKey);
        } catch (CacheNotFoundException) {
            $query = $this->db->select('data')
                ->from($this->model::get_table_name())
                ->where('payment_method_id', $paymentMethodId)
                ->where('whitelabel_id', $whitelabelId);

            /** @var Database_Result $result */
            $result = $query->execute();
            $paymentApiSettings = unserialize($result->as_array()[0]['data']) ?? [];

            Cache::set($cacheKey, $paymentApiSettings, Helpers_Time::DAY_IN_SECONDS);
        }

        return $paymentApiSettings;
    }
}
