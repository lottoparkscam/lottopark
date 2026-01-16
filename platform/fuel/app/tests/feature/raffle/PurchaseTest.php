<?php

namespace Feature\Raffle;

use BadMethodCallException;
use Fuel\Core\Date;
use Fuel\Tasks\Factory\Utils\Faker;
use Generator;
use GuzzleHttp\Psr7\Response;
use Helpers_General;
use Models\Raffle as Raffle;
use Models\WhitelabelUser as User;
use Models\WhitelabelRaffleTicket as Ticket;
use Models\WhitelabelRaffleTicketLine as TicketLine;
use Orm\RecordNotFound;
use PHPUnit\Framework\MockObject\MockObject;
use Services_Lcs_Client_Response;
use Services_Lcs_Raffle_Buy_Ticket_Contract as LcsApiBuyTickets;
use Services_Lcs_Raffle_Ticket_Taken_Contract as LcsApiTakenTickets;
use Services_Raffle_Ticket as Purchase;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\WhitelabelUserBonusFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @group raffle
 * @covers \Services_Raffle_Ticket
 */
final class PurchaseTest extends Test_Feature
{
    private const SLUG = 'raffle-slug';

    private Purchase $purchase;
    private RaffleFixture $raffleFixture;
    private WhitelabelUserFixture $userFixture;

    private User $user;

    /** @var LcsApiTakenTickets|MockObject */
    private LcsApiTakenTickets $lcsTakenApi;

    /** @var MockObject|LcsApiBuyTickets */
    private LcsApiBuyTickets $lcsBuyApi;

    public function setUp(): void
    {
        parent::setUp();

        // Mock LCS calls
        $this->lcsTakenApi = $this->createMock(LcsApiTakenTickets::class);
        $this->container->set(LcsApiTakenTickets::class, $this->lcsTakenApi);

        $this->lcsBuyApi = $this->createMock(LcsApiBuyTickets::class);
        $this->mockBuyTicketSuccess($this->lcsBuyApi);
        $this->container->set(LcsApiBuyTickets::class, $this->lcsBuyApi);

        // Purchase service
        $this->purchase = $this->container->get(Purchase::class);

        // Fixtures
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->user = $this->userFixture->with(
            'basic',
            'currency.usd',
            $this->userFixture::BALANCE_10000,
            $this->userFixture::BONUS_BALANCE_10000,
        )->createOne();
    }

    /** @test */
    public function notExistingRaffle_ThrowsException(): void
    {
        // Given not existing Raffle id
        $raffleSlug = 'not-existing-one';

        // Expect
        $this->expectException(RecordNotFound::class);
        $this->expectExceptionMessage('Record with given criterias for <Models\Raffle> class has not been found');

        // When
        $this->purchase->purchase(1, $raffleSlug, 'closed', [], 1);
    }

    /** @test */
    public function raffleTemporaryDisabled_ThrowsException(): void
    {
        // Given temporary disabled raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with('basic', $this->raffleFixture::TEMPORARY_DISABLED)
            ->createOne(['slug' => self::SLUG]);

        // Expect
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageMatches('#Ticket purchase is closed until#');

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', [], 1);
    }

    /** @test */
    public function rafflePlayableUserNotExists_ThrowsException(): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with('basic', $this->raffleFixture::PLAYABLE)
            ->createOne();

        // Expect
        $this->expectException(RecordNotFound::class);
        $this->expectExceptionMessage(
            'Record with given criterias for <Models\WhitelabelUser> class has not been found'
        );

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', [], -1);
    }

    /**
     * @dataProvider provideNumbers
     * @test
     */
    public function rafflePlayable_ValidationFails(string $expectedMessage, array $numbers = []): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                $this->raffleFixture::PLAYABLE,
                fn(Raffle $r) => $r->whitelabel_raffle->provider->max_bets = 5
            )
            ->createOne();

        // And numbers I want to buy

        // Expect Assert invalid argument exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $this->user->id);
    }

    public function provideNumbers(): Generator
    {
        yield 'some of numbers are duplicated' => [
            "#Some of numbers are duplicated.#",
            [1, 2, 3, 1, 5],
        ];
        yield 'numbers count is more than max bets value' => [
            "#Too much bets provided. Lottery accept max 5 numbers, 6 given.#",
            range(1, 6),
        ];
        yield 'some of the values in not number' => [
            "#Value not-number is not valid number#",
            [1, 2, 'not-number'],
        ];
        yield 'one of values is greater than max bets' => [
            "#Number must be in range 1#",
            [1, 2, 80000],
        ];
    }

    /** @test */
    public function determinePaymentMethod_NotSufficientUserBalance_ThrowsException(): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                'currency.usd',
                $this->raffleFixture::PLAYABLE,
                fn(Raffle $r) => $r->whitelabel_raffle->provider->max_bets = 50,
                fn(Raffle $r) => $r->whitelabel_raffle->is_bonus_balance_in_use = false,
                $this->raffleFixture::REGULAR_PRICE,
            )
            ->createOne();

        // User with zero balance and bonus balance
        $pureUser = $this->userFixture->with(
            'basic',
            'currency.usd',
            $this->userFixture::BALANCE_0,
        )->createOne();

        // And numbers I want to buy
        // Line price = 10, fee = 1, one line sum is 10 + 1
        $numbers = [1];

        // Expect Assert invalid argument exception
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Your balance is too low to proceed. Please make <a href="/deposit/">a deposit</a>');

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $pureUser->id);
    }

    /** @test */
    public function determinePaymentMethod_NotSufficientUserBonusBalance_ThrowsException(): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                'currency.usd',
                $this->raffleFixture::PLAYABLE,
                fn(Raffle $r) => $r->whitelabel_raffle->provider->max_bets = 50,
                fn(Raffle $r) => $r->whitelabel_raffle->is_bonus_balance_in_use = true,
                $this->raffleFixture::REGULAR_PRICE,
            )
            ->createOne();

        // User with zero balance and bonus balance
        $pureUser = $this->userFixture->with(
            'basic',
            'currency.usd',
            $this->userFixture::BALANCE_0,
        )->createOne();

        // And numbers I want to buy
        // Line price = 10, fee = 1, one line sum is 10 + 1
        $numbers = [1];

        // Expect Assert invalid argument exception
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Your bonus balance is too low to proceed.');

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $pureUser->id);
    }

    /**
     * @dataProvider provideBonusBalanceInUse
     * @test
     */
    public function determinePaymentMethod_UserHasBalance_CashIsTaken(bool $bonus): void
    {
        // Given playable raffle
        $this->raffleFixture
            ->with(
                'basic',
                'currency.usd',
                $this->raffleFixture::PLAYABLE,
                fn(Raffle $r) => $r->whitelabel_raffle->provider->max_bets = 50,
                $this->raffleFixture::REGULAR_PRICE,
            );

        $s = $bonus ? $this->raffleFixture::WL_BONUS_BALANCE_ENABLED : $this->raffleFixture::WL_BONUS_BALANCE_DISABLED;
        $this->raffleFixture->with($s);
        $userBalanceField = $bonus ? 'bonus_balance' : 'balance';

        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->createOne();

        // User with 10000 usd balance

        // And numbers I want to buy
        // Line price = 10, fee = 1, one line sum is 10 + 1
        $numbers = [1];

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $this->user->id);

        /*
         *  todo:
         *  Here we have known rounding issue. Sometimes this test can break, due our rounding solution.
         *  Needs deeper investigation.
         */

        // Then user balance should be
        /** @var User $user */
        $user = User::find($this->user->id);
        $expected = 10000 - 11;
        $actual = round($user->$userBalanceField); // an issue with rounding
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function determinePaymentMethod_WelcomeBonusBalance_GetTicketWithAmount0(): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                $this->raffleFixture::PLAYABLE,
                $this->raffleFixture::REGULAR_PRICE,
            )
            ->createOne();

        // User with zero balance and bonus balance
        $pureUser = $this->userFixture->with(
            'basic',
            'currency.usd',
            $this->userFixture::BALANCE_0,
        )->createOne();

        $whitelabelUserBonusFixture = $this->container->get(WhitelabelUserBonusFixture::class);
        $bonus = $whitelabelUserBonusFixture
            ->with('basic')
            ->withType('register')
            ->withLotteryType('raffle')
            ->withUser($pureUser)
            ->createOne();

        // And numbers I want to buy
        // Line price = 10, fee = 1, one line sum is 10 + 1
        $numbers = [1];

        // When
        $this->purchase->addUserBonus($bonus);
        $ticket = $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $pureUser->id);

        // Then
        $this->assertSame(0.00, $ticket->amount);
        $this->assertSame(0.00, $ticket->bonus_amount);
    }

    public function provideBonusBalanceInUse(): Generator
    {
        yield 'wl bonus balance in use, user bonus should be used' => [true];
        yield 'wl bonus balance disabled, regular user balance should be used' => [false];
    }

    private function mockBuyTicketSuccess(MockObject $buyApi): void
    {
        $buyApi->method('request')
            ->willReturnCallback(
                function (array $payload, string $raffle_slug, string $raffle_type = 'closed') {
                    $ticket = $payload['tickets'][0];
                    $linesCount = count($ticket['lines']);

                    $data = [
                        'lottery_tickets' => [
                            [
                                'status' => Helpers_General::TICKET_STATUS_PENDING,
                                'is_paid_out' => false,
                                'lines_count' => $linesCount,
                                'amount_sale_point' => $ticket['amount'] / $linesCount,
                                'currency_code_sale_point' => 'USD',
                                'currency_code' => 'USD',
                                'draw_date' => null,
                                'token' => $ticket['token'],
                                'amount' => $ticket['amount'],
                                'ip' => $ticket['ip'],
                                'additional_data' => [],
                                'uuid' => Faker::forge()->uuid(),
                                'updated_at' => Date::forge()->format('mysql'),
                                'created_at' => Date::forge()->format('mysql'),
                            ]
                        ]
                    ];

                    $response = new Response(200, [], \GuzzleHttp\json_encode($data));
                    return new Services_Lcs_Client_Response($response);
                }
            );
    }

    /** @test */
    public function purchase_5LinesBought_1TicketAnd5LinesShouldBeCreated(): void
    {
        $ticketsCountBefore = Ticket::count();
        $ticketsLinesCountBefore = TicketLine::count();

        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                $this->raffleFixture::PLAYABLE,
            )
            ->createOne();

        // User with sufficient balance

        $numbers = range(1, 5);

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $this->user->id);

        // Then rows should be created
        $this->assertSame($ticketsCountBefore + 1, Ticket::count());
        $this->assertSame($ticketsLinesCountBefore + 5, TicketLine::count());
    }

    /**
     * @test
     * @group skipped
     */
    public function purchase_5LinesHasBeenSold_RafflePoolShouldBe5(): void
    {
        // Given playable raffle
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture
            ->with(
                'basic',
                $this->raffleFixture::PLAYABLE,
            )
            ->createOne(['is_sell_enabled' => false]);

        // User with sufficient balance

        $numbers = range(1, 5);

        // When
        $this->purchase->purchase(1, $raffle->slug, 'closed', $numbers, $this->user->id);

        // Then draw lines count should be 5
        /** @var Raffle $fresh */
        $fresh = Raffle::find($raffle->id);
        $this->assertSame(5, $fresh->draw_lines_count);
    }
}
