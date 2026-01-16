<?php

declare(strict_types=1);

namespace Repositories;

use Fuel\Core\Database_Result;
use Models\WhitelabelPaymentMethodCustomize;
use Repositories\Orm\AbstractRepository;

class WhitelabelPaymentMethodCustomizeRepository extends AbstractRepository
{
    public function __construct(WhitelabelPaymentMethodCustomize $model)
    {
        parent::__construct($model);
    }

    public function getFullData(int $whitelabelPaymentMethodId, int $languageId): array
    {
        $query = $this->db->selectArray(['wpmc.*'])
            ->from(['whitelabel_payment_method_customize', 'wpmc'])
            ->join(['whitelabel_language', 'wl'], 'INNER')->on('wl.id', '=', 'wpmc.whitelabel_language_id')
            ->where('wpmc.whitelabel_payment_method_id', $whitelabelPaymentMethodId)
            ->where('wl.language_id', $languageId);

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array()[0] ?? [];
    }
}
