<?php

namespace Services\MiniGame\Game;

use Models\MiniGame;
use Services\MiniGame\AbstractMiniGameService;
use Services\MiniGame\Interface\MiniGameServiceInterface;

class GgWorldCoinFlipGameService extends AbstractMiniGameService implements MiniGameServiceInterface
{
    public const GAME_SLUG = MiniGame::GG_WORLD_COINFLIP_SLUG;
}
