<?php

namespace Modules\Mediacle\Repositories;

use Carbon\Carbon;
use Modules\Mediacle\Models\MediaclePlayerData;

interface PlayerRegistrationsByDateContract
{
    /**
     * @param int $whitelabelId
     * @param Carbon $date
     * @return MediaclePlayerData[]
     */
    public function findPlayerRegistrationsByDate(int $whitelabelId, Carbon $date): array;
}
