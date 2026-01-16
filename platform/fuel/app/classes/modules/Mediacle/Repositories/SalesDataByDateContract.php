<?php

namespace Modules\Mediacle\Repositories;

use Carbon\Carbon;
use Modules\Mediacle\Models\MediacleSalesData;

interface SalesDataByDateContract
{
    /**
     * @param int $whitelabelId
     * @param Carbon $date
     * @return MediacleSalesData[]
     */
    public function findSalesDataByDate(int $whitelabelId, Carbon $date): array;
}
