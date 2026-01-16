<?php

namespace Tests\Feature\Classes\Repositories;

use Repositories\SlotOpenGameRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Container;
use Fuel\Core\Str;
use Models\SlotGame;
use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

/** @property SlotGame[] $games */
final class SlotOpenGameRepositoryTest extends AbstractSlotegrator
{
    private SlotOpenGameRepository $slotOpenGameRepository;
    protected bool $shouldAutoCreateGame = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->slotOpenGameRepository = Container::get(SlotOpenGameRepository::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->whitelabelUser->delete();
    }

    /** @test */
    public function userHasChangedInLobbyUserInitGameShouldReturnFalse(): void
    {
        $this->createNewGame(Str::random(), true);
        $this->createSlotOpenGame(false);

        // before game change
        $actual = $this->slotOpenGameRepository->userHasChangedGameInLobby(
            $this->whitelabelUser->id,
            $this->whitelabelSlotProvider->id,
            $this->slotGame->id,
            $this->slotOpenGame->sessionId
        );
        $this->assertFalse($actual);
    }

    /** @test */
    public function userHasChangedGameInLobbyShouldReturnTrue(): void
    {
        $this->userHasChangedInLobbyUserInitGameShouldReturnFalse();

        $this->mockGameChange();
        // after game change
        $this->assertGameChange();
    }

    /** @test */
    public function userHasChangedGameInLobbyTwiceShouldReturnTrue(): void
    {
        $this->userHasChangedGameInLobbyShouldReturnTrue();

        $this->createNewGame(Str::random(), true, false);
        $this->userHasChangedGameInLobbyShouldReturnTrue();
    }

    /** @test */
    public function userHasChangedGameInLobbyCameBackToPreviousShouldReturnFalse(): void
    {
        $this->createNewGame(Str::random(), true);
        $this->createNewGame(Str::random(), true, true);

        $this->newSlotGame = $this->slotGame;
        $this->userHasChangedGameInLobbyShouldReturnTrue();
    }

    public function mockGameChange(): void
    {
        // 1st game is created in parent
        $this->createNewGame(Str::random(), true, true);
        $this->createSlotOpenGame(true);

        $actual = $this->slotOpenGameRepository->getIntCount(
            [
                new Model_Orm_Criteria_Where('slot_game_id', [$this->slotGame->id, $this->newSlotGame->id], 'IN'),
                new Model_Orm_Criteria_Where('session_id', 1234),
                new Model_Orm_Criteria_Where('whitelabel_user_id', $this->whitelabelUser->id),
                new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $this->whitelabelSlotProvider->id)
            ]
        );

        $expected = 2;

        $this->assertSame($expected, $actual);
    }

    private function assertGameChange(): void
    {
        $actual = $this->slotOpenGameRepository->userHasChangedGameInLobby(
            $this->whitelabelUser->id,
            $this->whitelabelSlotProvider->id,
            $this->newSlotGame->id,
            $this->slotOpenGame->sessionId
        );

        $this->assertFalse($actual);
    }
}
