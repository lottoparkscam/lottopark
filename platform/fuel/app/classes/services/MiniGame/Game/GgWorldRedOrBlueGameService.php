<?php

namespace Services\MiniGame\Game;

use Models\MiniGame;
use Services\MiniGame\AbstractMiniGameService;
use Services\MiniGame\Interface\MiniGameServiceInterface;

class GgWorldRedOrBlueGameService extends AbstractMiniGameService implements MiniGameServiceInterface
{
    public const GAME_SLUG = MiniGame::GG_WORLD_RED_OR_BLUE_SLUG;
}
