<?php

namespace Tests\Feature\Classes\Repositories;

use Models\{
    SlotGame,
    WhitelabelSlotProvider
};
use Repositories\{
    SlotGameRepository,
    WhitelabelSlotProviderRepository
};
use Tests\Fixtures\{
    SlotGameFixture,
    WhitelabelSlotGameOrderFixture,
    WhitelabelSlotProviderFixture
};
use Helpers\SlotHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Feature;

final class SlotGameRepositoryTest extends Test_Feature
{
    private const GAMES_COUNT = 5;
    private const TEST_PROVIDER = 'TEST_GAME_PROVIDER';

    private bool $isMobile = false;

    private int $whitelabelId;
    private array $slotGames;

    private WhitelabelSlotProvider $whitelabelSlotProvider;

    private SlotGameRepository $slotGameRepository;
    private WhitelabelSlotProviderRepository|MockObject $whitelabelSlotProviderRepository;

    private SlotGameFixture $slotGameFixture;
    private WhitelabelSlotGameOrderFixture $whitelabelSlotGameOrderFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelSlotProviderFixture = $this->container->get(WhitelabelSlotProviderFixture::class);

        $this->slotGameFixture = $this->container->get(SlotGameFixture::class);
        $this->whitelabelSlotGameOrderFixture = $this->container->get(WhitelabelSlotGameOrderFixture::class);

        $this->whitelabelSlotProviderRepository = $this->createMock(WhitelabelSlotProviderRepository::class);

        $this->slotGameRepository = new SlotGameRepository(
            $this->container->get(SlotGame::class),
            $this->whitelabelSlotProviderRepository
        );

        $this->whitelabelSlotProvider = $this->whitelabelSlotProviderFixture
            ->with(
                WhitelabelSlotProviderFixture::WHITELABEL,
                WhitelabelSlotProviderFixture::SLOT_PROVIDER
            )
            ->createOne();

        $this->whitelabelId = $this->whitelabelSlotProvider->whitelabelId;

        $this->slotGames = $this->slotGameFixture
            ->with('basic')
            ->createMany([
                'slot_provider_id' => $this->whitelabelSlotProvider->slotProviderId,
                'provider' => self::TEST_PROVIDER
            ], self::GAMES_COUNT);

        $this->whitelabelSlotProviderRepository
            ->method('getAllowedSubprovidersNamesByWhitelabelId')
            ->willReturn([self::TEST_PROVIDER]);
    }

    public function gamesOrderProvider(): array
    {
        return [
            'correct order' => [[0, 1, 2, 3, 4]],
            'random order' => [[2, 0, 1, 4, 3]],
            'random order less than games count' => [[2, 0, 1]],
            'random order greater than games count' => [[2, 0, 1, 4, 3, 5, 6]],
        ];
    }

    /**
     * @test
     * @dataProvider gamesOrderProvider
     */
    public function findEnabledGamesForSlotProviders_ShouldReturnGamesInTheCorrectOrder(array $gamesOrder): void
    {
        $gamesOrderRandom = $this->createOrderJsonForGames($this->slotGames, $gamesOrder);

        $this->whitelabelSlotGameOrderFixture->with('basic')->createOne([
            'whitelabel_id' => $this->whitelabelId,
            'order_json' => json_encode(['homepage' => $gamesOrderRandom])
        ]);

        $slotGames = $this->slotGameRepository->findEnabledGamesForSlotProviders(
            $this->whitelabelId,
            [$this->whitelabelSlotProvider->slotProviderId],
            $this->isMobile,
        );

        $expected = SlotHelper::getGamesIdsSortedByGameOrder($gamesOrderRandom);

        $actual = array_column($slotGames, 'id');

        /**
         * When the count of 'gamesOrder' items is less than the retrieved count of games
         * we only compare matching IDs.
         */
        $actual = array_intersect_key($actual, $expected);

        $this->assertSame($expected, $actual);
    }

    private function createOrderJsonForGames(array $games, array $order): array
    {
        $gamesOrder = [];

        foreach ($order as $value) {
            foreach ($games as $game) {
                $gameID = $game->id;

                if (isset($gamesOrder[$gameID])) {
                    continue;
                }

                $gamesOrder[$gameID]['gameOrder'] = $value;
                $gamesOrder[$gameID]['gameId'] = $game->id;

                break;
            }
        }

        return array_values($gamesOrder);
    }
}
