<?php

namespace Services\MiniGame\Factory;

use InvalidArgumentException;
use Models\MiniGame;
use Services\MiniGame\Game\GgWorldCoinFlipGameService;
use Services\MiniGame\Game\GgWorldRedOrBlueGameService;
use Services\MiniGame\Game\GgWorldSantaInDaHouseGameService;
use Services\MiniGame\Game\GgWorldTicTacBooGameService;
use Services\MiniGame\Interface\MiniGameServiceInterface;

class MiniGameServiceFactory
{
    protected array $gameServices;

    public function __construct(
        GgWorldCoinFlipGameService $ggWorldCoinFlipGameService,
        GgWorldTicTacBooGameService $ggWorldTicTacBooGameService,
        GgWorldSantaInDaHouseGameService $ggWorldSantaInDaHouseGameService,
        GgWorldRedOrBlueGameService $ggWorldRedOrBlueGameService,
    )
    {
        $this->gameServices = [
            MiniGame::GG_WORLD_COINFLIP_SLUG => $ggWorldCoinFlipGameService,
            MiniGame::GG_WORLD_TIC_TAC_BOO_SLUG => $ggWorldTicTacBooGameService,
            MiniGame::GG_WORLD_SANTA_IN_DA_HOUSE_SLUG => $ggWorldSantaInDaHouseGameService,
            MiniGame::GG_WORLD_RED_OR_BLUE_SLUG => $ggWorldRedOrBlueGameService,
        ];
    }

    public function getServiceBySlug(string $slug): MiniGameServiceInterface
    {
        if (!isset($this->gameServices[$slug])) {
            throw new InvalidArgumentException("[MiniGame] Game with slug {$slug} not found.");
        }

        return $this->gameServices[$slug];
    }
}
