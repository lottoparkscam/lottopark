<?php

namespace Tests\Feature\Classes\Repositories;

use Models\Whitelabel;
use Models\WhitelabelUserTicket;
use Repositories\Orm\WhitelabelUserRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Tests\Fixtures\
{WhitelabelFixture, WhitelabelUserFixture, WhitelabelUserTicketFixture};
use Test_Feature;
use Container;

final class WhitelabelUserRepositoryTest extends Test_Feature
{
    private Whitelabel $whitelabel;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUserTicketFixture $whitelabelUserTicketFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelUserFixture =  Container::get(WhitelabelUserFixture::class);
        $this->whitelabelUserTicketFixture =  Container::get(WhitelabelUserTicketFixture::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->whitelabelFixture =  Container::get(WhitelabelFixture::class);
        $this->whitelabel = $this->whitelabelFixture->createOne();
    }

    /**
     * @test
     */
    public function getUsersAfterId_WhenAfterIdIsNull_ShouldReturnAllUsers(): void
    {
        $numberOfUsers = 5;
        $expected = 5;

        $modelsSaved = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 2
            ], $numberOfUsers);

        $currentModelExpected = current($modelsSaved);

        $afterId = null;

        $actual = $this->whitelabelUserRepository->getUsersAfterId($this->whitelabel->id, $afterId);

        $currentModelActual = current($actual);

        $this->assertCount($expected, $actual);
        $this->assertSame($currentModelExpected->id, $currentModelActual->id);
    }

    public function getCriteriasDataProvider(): array
    {
        return [
            'empty criterias -> all records' => [5, []],
            'matching criterias -> all records' => [5, [new Model_Orm_Criteria_Where('country', 'PL')]],
            'non-matching criterias -> 0 records' => [0, [new Model_Orm_Criteria_Where('country', 'FR')]],
            'partially matching criterias -> 3 records' => [3, [new Model_Orm_Criteria_Where('currency_id', '2')]]
        ];
    }

    /**
     * @test
     * @dataProvider getCriteriasDataProvider
     */
    public function getUsersAfterId_WithAdditionalCriterias(int $expectedCount, array $criterias): void
    {
        $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 1,
                'country' => 'PL'
            ], 2);

        $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 2,
                'country' => 'PL'
            ], 3);

        $afterId = null;

        $actual = $this->whitelabelUserRepository->getUsersAfterId($this->whitelabel->id, $afterId, 100, $criterias);

        $this->assertCount($expectedCount, $actual);
    }

    /**
     * @test
     */
    public function getUsersAfterId_WhenAfterIdIsNotNull_ShouldNotReturnAllUsers(): void
    {
        $numberOfUsers = 5;
        $expected = 2;

        $modelsSaved = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 2
            ], $numberOfUsers);

        next($modelsSaved);
        next($modelsSaved);

        $currentModelExpected = current($modelsSaved);

        next($modelsSaved);

        $nextModelExpected = current($modelsSaved);

        $afterId = $currentModelExpected->id;

        $actual = $this->whitelabelUserRepository->getUsersAfterId($this->whitelabel->id, $afterId);

        $currentModelActual = current($actual);

        $this->assertCount($expected, $actual);
        $this->assertSame($nextModelExpected->id, $currentModelActual->id);
    }

    /**
     * @test
     */
    public function getUsersAfterId_WhenAfterIdPointsToTheLastUser_ShouldReturnEmptyResult(): void
    {
        $numberOfUsers = 5;
        $expected = 0;

        $modelsSaved = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 2
            ], $numberOfUsers);

        end($modelsSaved);

        $currentModelExpected = current($modelsSaved);

        $afterId = $currentModelExpected->id;

        $actual = $this->whitelabelUserRepository->getUsersAfterId($this->whitelabel->id, $afterId);

        $this->assertCount($expected, $actual);
        $this->assertEmpty($actual);
    }

    /**
     * @test
     */
    public function getLastUserId_WhenWhitelabelUsersDoNotExist_ShouldReturnNull(): void
    {
        $actual = $this->whitelabelUserRepository->getLastUserId($this->whitelabel->id);

        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function getLastUserId_WhenWhitelabelUsersExist_ShouldReturnCorrectId(): void
    {
        $numberOfUsers = 5;

        $modelsSaved = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createMany([
                'whitelabel_id' => $this->whitelabel->id,
                'currency_id' => 2
            ], $numberOfUsers);

        end($modelsSaved);

        $currentModelExpected = current($modelsSaved);

        $actual = $this->whitelabelUserRepository->getLastUserId($this->whitelabel->id);

        $this->assertNotNull($actual);
        $this->assertSame($currentModelExpected->id, $actual);
    }

    /** @test */
    public function getLastPurchasedLotteryNameByUsersIds_withPaidTicket(): void
    {
        // Given
        $ticket = $this->whitelabelUserTicketFixture
            ->with(WhitelabelUserTicketFixture::BASIC, WhitelabelUserTicketFixture::PAID)
            ->createOne();


        // When
        $actual = $this->whitelabelUserRepository->getLastPurchasedLotteryNameByUsersIds(
            [$ticket->whitelabelUserId]
        );

        // Then
        $expected = 'Powerball';
        $this->assertArrayHasKey($ticket->whitelabelUserId, $actual);
        $this->assertSame($expected, $actual[$ticket->whitelabelUserId]);
    }

    /** @test */
    public function getLastPurchasedLotteryNameByUsersIds_withoutTicket(): void
    {
        // Given
        $user = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        // When
        $actual = $this->whitelabelUserRepository->getLastPurchasedLotteryNameByUsersIds(
            [$user->id]
        );

        // Then
        $expected = null;
        $this->assertArrayHasKey($user->id, $actual);
        $this->assertSame($expected, $actual[$user->id]);
    }

    /** @test */
    public function getLastPurchasedLotteryNameByUsersIds_withNotPaidTicket(): void
    {
        // Given
        $ticket = $this->whitelabelUserTicketFixture
            ->with(WhitelabelUserTicketFixture::BASIC, WhitelabelUserTicketFixture::PAID)
            ->createOne();
        $ticket = WhitelabelUserTicket::find($ticket->id);
        $ticket->whitelabelTransaction = null;
        $ticket->whitelabelTransactionId = null;
        $ticket->save();


        // When
        $actual = $this->whitelabelUserRepository->getLastPurchasedLotteryNameByUsersIds(
            [$ticket->whitelabelUserId]
        );

        // Then
        $expected = null;
        $this->assertArrayHasKey($ticket->whitelabelUserId, $actual);
        $this->assertSame($expected, $actual[$ticket->whitelabelUserId]);
    }
}
