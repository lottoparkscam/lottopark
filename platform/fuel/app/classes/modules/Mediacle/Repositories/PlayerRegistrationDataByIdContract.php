<?php

namespace Modules\Mediacle\Repositories;

use Modules\Mediacle\Models\MediaclePlayerRegistrationData;

interface PlayerRegistrationDataByIdContract
{
    public function getPlayerById(int $playerId): MediaclePlayerRegistrationData;
}
