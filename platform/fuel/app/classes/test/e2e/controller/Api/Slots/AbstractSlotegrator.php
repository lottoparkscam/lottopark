<?php

namespace Test\E2e\Controller\Api\Slots;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Controller_Api_Slots_Slotegrator;
use Factory_Orm_Whitelabel_User;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\SlotGame;
use Models\SlotLog;
use Models\SlotOpenGame;
use Models\SlotProvider;
use Models\SlotTransaction;
use Models\WhitelabelSlotProvider;
use Models\SlotWhitelistIp;
use Services\Api\Slots\Providers\SlotegratorSecurityService;
use Test_E2e_Controller_Api;
use Wrappers\Orm;
use Container;
use Repositories\SlotGameRepository;

abstract class AbstractSlotegrator extends Test_E2e_Controller_Api
{
    protected Whitelabel $whitelabel;
    protected WhitelabelUser $whitelabelUser;
    protected SlotProvider $slotProvider;
    protected WhitelabelSlotProvider $whitelabelSlotProvider;
    protected SlotegratorSecurityService $slotegratorSecurityService;
    protected SlotGame $slotGame;
    protected SlotGame $newSlotGame;
    protected SlotOpenGame $slotOpenGame;
    protected SlotOpenGame $newSlotOpenGame;
    protected $in_transaction = false;
    private SlotWhitelistIp $slotWhitelistIp;
    private float $previousBalance;
    private float $previousSlotProviderLimit;
    private array $modelsToDelete = [];
    protected bool $shouldAutoCreateGame = true;
    protected array $createdGamesIds = [];
    protected SlotGameRepository $slotGameRepository;

    public function setUp(): void
    {
        parent::setUp();
        Orm::disableCaching();
        $this->slotGameRepository = Container::get(SlotGameRepository::class);

        $user = WhitelabelUser::find('first');
        if (empty($user)) {
            $userFactory = Factory_Orm_Whitelabel_User::forge();
            $user = $userFactory->build();
            $this->modelsToDelete[] = $this->whitelabelUser;
        }

        $this->whitelabelUser = $user;

        $this->previousBalance = $this->whitelabelUser->casinoBalance;
        $this->whitelabelUser->casinoBalance = 100;
        $this->whitelabelUser->isDeleted = false;
        $this->whitelabelUser->save();

        $this->whitelabel = $this->whitelabelUser->whitelabel;

        $slotProvider = SlotProvider::find('first', [
            'where' => [
                'slug' => 'slotegrator'
            ]
        ]);

        if(empty($slotProvider)){
            $slotProvider = new SlotProvider();
            $slotProvider->slug = 'slotegrator';
            $slotProvider->save();
        }

        $this->slotProvider = $slotProvider;

        $whitelabelSlotProvider = WhitelabelSlotProvider::find('first', [
            'where' => [
                'whitelabel_id' => $this->whitelabel->id,
                'slot_provider_id' => $this->slotProvider->id
            ]
        ]);

        if(empty($whitelabelSlotProvider)){
            $whitelabelSlotProvider = new WhitelabelSlotProvider();
            $whitelabelSlotProvider->whitelabelId = $this->whitelabel->id;
            $whitelabelSlotProvider->slotProviderId = $this->slotProvider->id;
        }
        $whitelabelSlotProvider->isEnabled = true;
        $whitelabelSlotProvider->save();
        $this->whitelabelSlotProvider = $whitelabelSlotProvider;

        $this->previousSlotProviderLimit = $this->whitelabelSlotProvider->maxMonthlyMoneyAroundUsd;

        $slotWhitelistIp = new SlotWhitelistIp();
        $slotWhitelistIp->ip = $this->ip;
        $slotWhitelistIp->slotProvider = $this->slotProvider;
        $slotWhitelistIp->save();
        $this->slotWhitelistIp = $slotWhitelistIp;
        $this->slotegratorSecurityService = Container::get(SlotegratorSecurityService::class);
        if ($this->shouldAutoCreateGame) {
            $this->createNewGame();
            $this->createSlotOpenGame();
        }
    }

    public function createSlotOpenGame(bool $isNewGame = false): void
    {
        $slotOpenGame = new SlotOpenGame();
        $slotOpenGame->whitelabelUser = $this->whitelabelUser;
        $slotOpenGame->slotGame = $isNewGame ? $this->newSlotGame : $this->slotGame;
        $slotOpenGame->sessionId = 1234;
        $slotOpenGame->createdAt = new Carbon();
        $slotOpenGame->whitelabelSlotProvider = $this->whitelabelSlotProvider;
        $slotOpenGame->currencyId = 1;
        $slotOpenGame->save();

        if ($isNewGame) {
            $this->newSlotOpenGame = $slotOpenGame;
        } else {
            $this->slotOpenGame = $slotOpenGame;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->whitelabelUser->casinoBalance = $this->previousBalance;
        $this->whitelabelUser->save();
        $this->clearWhitelistIp();

        if (!empty($this->createdGamesIds)) {
            $createdGames = $this->slotGameRepository->pushCriteria(new Model_Orm_Criteria_Where('id', $this->createdGamesIds, 'IN'))->getResults();
            foreach ($createdGames as $createdGame) {
                $createdGame->delete();
            }
        }

        if (!empty($this->slotGame)) {
            $this->slotGame->delete();
        }

        if (!empty($this->newSlotGame)) {
            $this->newSlotGame->delete();
        }

        $logs = SlotLog::find('all');
        foreach ($logs as $log) {
            $log->delete();
        }

        $transactions = SlotTransaction::find('all');
        foreach ($transactions as $transaction) {
            $transaction->delete();
        }

        $this->whitelabelSlotProvider->maxMonthlyMoneyAroundUsd = $this->previousSlotProviderLimit;
        $this->whitelabelSlotProvider->isLimitEnabled = false;
        $this->whitelabelSlotProvider->save();

        foreach ($this->modelsToDelete as $model) {
            $model->delete();
        }
    }


    protected function checkErrors(
        string $errorMessage,
        array $postData = [],
        array $headers = [],
        string $code = Controller_Api_Slots_Slotegrator::INTERNAL_ERROR_CODE
    ) {
        $response = $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $headers,
            [
                'form_params' => $postData
            ]
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertSame($code, $body['error_code']);
        $this->assertSame($errorMessage, $body['error_description']);
    }

    protected function checkSuccess(array $responseBody, array $postData = [], array $headers = [])
    {
        $response = $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $headers,
            [
                'form_params' => $postData
            ]
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertSame($responseBody, $body);
    }

    protected function clearWhitelistIp(): void
    {
        if (!empty($this->slotWhitelistIp)) {
            $this->slotWhitelistIp->delete();
        }
    }

    protected function createNewGame(
        string $uuid = 'abcd',
        bool $hasLobby = false,
        bool $createNextNewGame = false
    ): void
    {
        $slotGame = new SlotGame();
        $slotGame->slotProvider = $this->slotProvider;
        $slotGame->uuid = $uuid;
        $slotGame->name = 'Test';
        $slotGame->provider = 'Test';
        $slotGame->image = 'test';
        $slotGame->type = 'test';
        $slotGame->technology = 'html5';
        $slotGame->hasLobby = $hasLobby;
        $slotGame->isMobile = false;
        $slotGame->hasFreespins = false;
        $slotGame->freespinValidUntilFullDay = false;
        $slotGame->save();

        $this->createdGamesIds[] = $slotGame->id;
        if ($createNextNewGame) {
            $this->newSlotGame = $slotGame;
        } else {
            $this->slotGame = $slotGame;
        }
    }

    protected function getDefaultRequestParams(array $params = []): array
    {
        return array_merge([
            'action' => 'bet',
            'game_uuid' => 'abcd',
            'amount' => 80.0,
            'currency' => 'EUR',
            'player_id' => $this->whitelabelUser->token,
            'transaction_id' => 'test123',
            'session_id' => 1234,
            'type' => 'bet'
        ], $params);
    }

    protected function checkLog(array $conditions = []): void
    {
        $slotLog = SlotLog::find('first', [
            'where' => array_merge([
                'whitelabel_slot_provider_id' => $this->whitelabelSlotProvider->id,
                'slot_game_id' => $this->slotGame->id,
                'whitelabel_user_id' => $this->whitelabelUser->id,
                'action' => 'balance',
                'is_error' => false
            ], $conditions)
        ]);

        $this->assertInstanceOf(SlotLog::class, $slotLog);
    }

    protected function checkTransaction(float $amountUsd, float $amountManager, array $conditions = []): SlotTransaction
    {
        /** @var SlotTransaction $slotTransaction */
        $slotTransaction = SlotTransaction::find('first', [
            'where' => array_merge([
                'whitelabel_slot_provider_id' => $this->whitelabelSlotProvider->id,
                'currency_id' => 2, // in each request we sent in params currency => EUR
                'slot_game_id' => $this->slotGame->id,
                'whitelabel_user_id' => $this->whitelabelUser->id,
                'amount' => 80,
                'is_canceled' => false,
                'provider_transaction_id' => 'test123',
                'type' => 'bet'
            ], $conditions)
        ]);

        $this->assertInstanceOf(SlotTransaction::class, $slotTransaction);
        $this->assertIsNumeric($slotTransaction->token);
        $this->assertGreaterThan(1000, $slotTransaction->token);
        $this->assertSame($amountUsd, $slotTransaction->amountUsd);
        $this->assertSame($amountManager, $slotTransaction->amountManager);

        return $slotTransaction;
    }

    protected function checkUserBalance(float $balance): void
    {
        WhitelabelUser::flush_cache();
        $this->whitelabelUser = WhitelabelUser::find($this->whitelabelUser->id);
        $this->assertSame($balance, $this->whitelabelUser->casinoBalance);
    }
}
