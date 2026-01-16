<?php

namespace Tests\E2e\Controller\Api\Slots\Slotegrator;

use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;
use Repositories\SlotOpenGameRepository;
use Repositories\SlotTransactionRepository;
use Container;
use Models\SlotTransaction;

final class LobbyTest extends AbstractSlotegrator
{
    private SlotOpenGameRepository $slotOpenGameRepository;
    private SlotTransactionRepository $slotTransactionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->slotOpenGameRepository = Container::get(SlotOpenGameRepository::class);
        $this->slotTransactionRepository = Container::get(SlotTransactionRepository::class);
    }

    /**
     * Test just bet case, other methods has common logic
     * @test
     */
    public function userChangedGameInLobbyShouldInsertValidGameUuid(): void
    {
        $requestParams = $this->getDefaultRequestParams();
        $this->slotGame->hasLobby = true;
        $this->slotGame->save();

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        // set new game
        $this->createNewGame('abcdef', true, true);

        $newRequestParams = $this->getDefaultRequestParams([
            'game_uuid' =>  $this->newSlotGame->uuid,
            'provider_transaction_id' => 'test12345',
            'amount' => 1.0,
            'transaction_id' => '192838129312'
        ]);
        SlotTransaction::flush_cache();

        // send request with the same session but different game
        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $newRequestParams),
            [
                'form_params' => $newRequestParams
            ]
        );

        $this->checkTransaction(95.03, 80.0, ['slot_game_id' => $this->slotGame->id]);
        $this->checkUserBalance(3.78);

        $expectedGameIds = [$this->slotGame->id, $this->newSlotGame->id];
        $actualSlotOpenGames = $this->slotOpenGameRepository->pushCriterias([
            new Model_Orm_Criteria_Where('session_id', $requestParams['session_id']),
            new Model_Orm_Criteria_Where('slot_game_id', $expectedGameIds, 'IN')
        ])->getResults();

        $expectedCount = 2;
        $this->assertCount($expectedCount, $actualSlotOpenGames);

        $expectedSlotOpenGameIds = [];
        foreach ($actualSlotOpenGames as $actualSlotOpenGame) {
            $expectedSlotOpenGameIds[] = $actualSlotOpenGame->id;
        }
        // check if transaction has been successfully created with correct data

        $slotTransactions = $this->slotTransactionRepository->pushCriterias([
            new Model_Orm_Criteria_Where('slot_open_game_id', $expectedSlotOpenGameIds, 'IN'),
            new Model_Orm_Criteria_Where('slot_game_id', $expectedGameIds, 'IN'),
            new Model_Orm_Criteria_Order('created_at')
        ])->getResults();

        $this->assertCount(2, $slotTransactions);
    }
}
