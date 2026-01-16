<?php

namespace Services\MiniGame\Interface;

use Models\WhitelabelUser;
use Services\MiniGame\Dto\GamePlayResult;
use Services\MiniGame\Dto\MiniGameData;

interface MiniGameServiceInterface
{
    public function fetchMiniGameData(WhitelabelUser $user, string $slug): MiniGameData;
    public function play(WhitelabelUser $user, string $slug, int $selectedNumber, float $betAmountInEur): GamePlayResult;
}