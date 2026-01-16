<?php

namespace Modules\Mediacle\ApiRepository;

use Modules\Mediacle\Models\MediaclePlayerRegistrationData;

interface SavePlayerContract
{
    public function save(MediaclePlayerRegistrationData $player): void;
}
