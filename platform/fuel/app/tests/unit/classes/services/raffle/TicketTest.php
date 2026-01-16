<?php

namespace Tests\Unit\Classes\Services\Raffle;

use BadMethodCallException;
use Exception;
use Fuel\Tasks\Factory\Utils\Faker;
use Models\Raffle;
use Models\WhitelabelUser;
use Models\WhitelabelRaffleTicket;
use Modules\Account\Balance\BalanceStrategyResolver;
use Modules\Account\Balance\BonusBalance;
use Modules\Account\Balance\InteractsWithBalance;
use Modules\Account\Balance\RegularBalance;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\Orm\RaffleRepository;
use Services\Shared\System;
use Services_Currency_Calc;
use Services_Lcs_Raffle_Buy_Ticket_Contract;
use Services_Lcs_Raffle_Buy_Ticket_Mock as BuyTicketsApiMock;
use Services_Lcs_Raffle_Ticket_Taken_Contract as TakenTicketsApi;
use Services_Raffle_Factory_Ticket;
use Services_Raffle_Number_Validator;
use Services_Raffle_Ticket;
use Services_Raffle_Token_Ticket_Resolver;
use Helpers_General;
use Test_Unit;
use Wrappers\Db;
use Services\Logs\FileLoggerService;

/**
 * @covers Services_Raffle_Ticket
 */
final class TicketTest extends Test_Unit
{
    private Services_Lcs_Raffle_Buy_Ticket_Contract $buy_ticket_api;
    private BuyTicketsApiMock $buyTicketApiMock;
    private TakenTicketsApi $taken_tickets_api;
    private WhitelabelUser $user_dao;
    private Raffle $raffle_dao;
    private WhitelabelRaffleTicket $ticket_dao;
    private Services_Raffle_Token_Ticket_Resolver $ticket_token_resolver;
    private Services_Currency_Calc $currency_calc;
    private Services_Raffle_Factory_Ticket $ticket_factory;
    private Services_Raffle_Number_Validator $number_validator;
    private Db $db;
    private Services_Raffle_Ticket $service;
    private System $system;
    private RaffleRepository $raffleRepo;
    private BalanceStrategyResolver $balance_resolver;
    private FileLoggerService $fileLoggerService;

    private $buy_json = [];
    /** @var MockObject|RegularBalance */
    private $regular_balance_strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->buy_ticket_api = $this->createMock(Services_Lcs_Raffle_Buy_Ticket_Contract::class);
        $this->buyTicketApiMock = $this->container->get(BuyTicketsApiMock::class);
        $this->taken_tickets_api = $this->createMock(TakenTicketsApi::class);
        $this->user_dao = $this->createMock(WhitelabelUser::class);
        $this->raffle_dao = $this->createMock(Raffle::class);
        $this->ticket_dao = $this->createMock(WhitelabelRaffleTicket::class);
        $this->ticket_token_resolver = $this->createMock(Services_Raffle_Token_Ticket_Resolver::class);
        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
        $this->ticket_factory = $this->createMock(Services_Raffle_Factory_Ticket::class);
        $this->number_validator = $this->createMock(Services_Raffle_Number_Validator::class);
        $this->system = $this->createMock(System::class);
        $this->db = $this->createMock(Db::class);
        $this->raffleRepo = $this->createMock(RaffleRepository::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        # some generic logic to avoid multiple test modifications
        $this->balance_resolver = $this->createStub(BalanceStrategyResolver::class);

        $this->regular_balance_strategy = $this->createMock(RegularBalance::class);
        $this->regular_balance_strategy->method('hasSufficientBalanceToProcess')->willReturn(true);
        $this->regular_balance_strategy->method('hasSufficientBalanceToProcessSingular')->willReturn(true);

        $this->balance_resolver->method('determinePaymentMethod')
            ->will($this->returnCallback(function (InteractsWithBalance $service, Raffle $raffle, WhitelabelUser $user, float $required_amount_in_user_currency) {
                $service->setBalanceStrategy($this->regular_balance_strategy);
            }));

        $this->buy_json = json_decode(
            file_get_contents(
                APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, 'tests\data\lcs\buy_ticket_response.json')
            ),
            true
        );

        $this->createService();
    }

    # get_and_verify_raffle

    /** @test */
    public function getAndVerifyRaffle_RaffleSellIsTmpDisabled_ThrowsException(): void
    {
        $raffle = $this->get_raffle([
            'is_sell_limitation_enabled' => true,
            'is_sell_enabled' => false,
            'sell_open_dates' => [
                "Mon 23:59",
                "Tue 23:59",
                "Wed 23:59",
                "Thu 23:59",
                "Fri 23:59",
                "Sat 23:59",
                "Sun 23:59"
            ]
        ]);

        $this->raffle_dao->expects($this->once())
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $dates = $raffle->sell_open_dates_objects;
        $closed_until = reset($dates);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf('Ticket purchase is closed until %s', $closed_until->format('Y-m-d H:i')));

        $this->service->purchase(1, $raffle->slug, 'closed', [], 1);
    }

    /** @test */
    public function getAndVerifyRaffle_SellIsNotEnabled_ThrowsException(): void
    {
        // Expect
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Lottery is temporary not playable due prizes calculation.');

        $raffle = $this->get_raffle([
            'is_sell_limitation_enabled' => false,
            'is_sell_enabled' => false,
        ]);

        $this->raffle_dao->expects($this->once())
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $this->service->purchase(1, $raffle->slug, 'closed', [], 1);
    }

    /** @test */
    public function verifyLcsCanBuy_DuplicatedNumbers_ThrowsException(): void
    {
        $numbers = range(1, 100);
        $raffle = $this->get_raffle();
        $raffle->whitelabel_raffle->is_bonus_balance_in_use = false;

        # start_transaction
        $this->db->expects($this->never())->method('start_transaction');
        $this->db->expects($this->never())->method('rollback_transaction');
        $this->db->expects($this->never())->method('commit_transaction');

        $this->raffle_dao->expects($this->once())
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $user = $this->get_user();
        $this->user_dao->expects($this->once())->method('get_user_by_id')->with($user->id)->willReturn($user);

        $this->number_validator->expects($this->once())->method('validate')->with($raffle, $numbers)->willReturn($numbers);

        $required_amount = $this->calculateTicketAmount($raffle, $numbers);
        $this->currency_calc->expects($this->once())->method('convert_to_any')
            ->with($required_amount, $raffle->getFirstRule()->currency->code, $user->currency->code)
            ->willReturn($required_amount);

        $this->taken_tickets_api->expects($this->once())->method('request')
            ->with($raffle->slug, 'type')->willReturn(
                $this->mock_lcs_response([
                    'data' => [
                        'taken_numbers' => range(1, 5)
                    ]
                ])
            );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Given numbers <1, 2, 3, 4, 5> has been purchased by someone else. Please select new numbers.');

        $this->service->purchase(1, $raffle->slug, 'type', $numbers, $user->id);
    }

    /** @test */
    public function lcsBuy_LcsBuySuccessDbFails_ThrowsExceptionLogsAndRollbacks(): void
    {
        $exception = new Exception('Test');
        $numbers = range(1, 100);
        $raffle = $this->get_raffle();
        $raffle->whitelabel_raffle->is_bonus_balance_in_use = false;

        $this->raffle_dao->expects($this->once())
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $user = $this->get_user();
        $this->user_dao->expects($this->once())->method('get_user_by_id')->with($user->id)->willReturn($user);

        $this->number_validator->expects($this->once())->method('validate')->with($raffle, $numbers)->willReturn($numbers);

        $amount_with_fee = $this->calculateTicketAmount($raffle, $numbers);
        $this->currency_calc->expects($this->once())->method('convert_to_any')
            ->with($amount_with_fee, $raffle->getFirstRule()->currency->code, $user->currency->code)
            ->willReturn($amount_with_fee);

        $this->taken_tickets_api->expects($this->once())->method('request')
            ->with($raffle->slug, 'type')->willReturn(
                $this->mock_lcs_response([
                    'data' => [
                        'taken_numbers' => []
                    ]
                ])
            );

        $token = Faker::forge()->numberBetween(1000, 100000);

        $this->ticket_token_resolver->expects($this->once())->method('issue')
            ->with(1)->willReturn($token);

        $amount_without_fee = $amount_with_fee - ($raffle->getFirstRule()->fee * count($numbers));
        $lcs_ticket = [
            array_merge($this->buy_json[0], ['amount' => $amount_without_fee, 'lines_count' => count($numbers)])
        ];

        $this->buy_ticket_api->expects($this->once())->method('request')
            ->with([
                'tickets' => [
                    [
                        'token'  => $token,
                        'amount' => $amount_without_fee,
                        'ip'     => $user->last_ip,
                        'lines'  => array_map(function (int $number) {
                            return ['numbers' => [[$number]]];
                        }, $numbers)
                    ]
                ]
            ], $raffle->slug)->willReturn(
                $this->mock_lcs_response([
                    'lottery_tickets' => $lcs_ticket
                ])
            );

        $amount_with_fee_in_user_currency = $amount_with_fee / 2;

        # start_transaction
        $this->db->expects($this->once())->method('start_transaction');
        $this->db->expects($this->once())->method('rollback_transaction');
        $this->db->expects($this->never())->method('commit_transaction');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->ticket_factory->expects($this->once())->method('create_from_lcs_ticket_data')
            ->with(1, $lcs_ticket[0], $raffle, $user, $numbers, $this->regular_balance_strategy)
            ->willThrowException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test');

        $this->service->purchase(1, $raffle->slug, 'type', $numbers, $user->id);
    }

    /** @test */
    public function disableRaffle_PoolIsSoldOut_RaffleIsDisabled(): void
    {
        $numbers = range(1, 100);
        $raffle = $this->get_raffle(['is_sell_limitation_enabled' => true]);
        $raffle->is_sell_limitation_enabled = 1;

        $raffle->whitelabel_raffle->is_bonus_balance_in_use = false;

        $this->raffle_dao->expects($this->once())
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $user = $this->get_user();
        $this->user_dao->expects($this->once())->method('get_user_by_id')->with($user->id)->willReturn($user);

        $this->number_validator->expects($this->once())->method('validate')->with($raffle, $numbers)->willReturn($numbers);

        $amount_with_fee = $this->calculateTicketAmount($raffle, $numbers);
        $this->currency_calc->expects($this->once())->method('convert_to_any')
            ->with($amount_with_fee, $raffle->getFirstRule()->currency->code, $user->currency->code)
            ->willReturn($amount_with_fee);

        $this->taken_tickets_api->expects($this->once())->method('request')
            ->with($raffle->slug, 'type')->willReturn(
                $this->mock_lcs_response([
                    'data' => [
                        'taken_numbers' => []
                    ]
                ])
            );

        $token = Faker::forge()->numberBetween(1000, 100000);

        $this->ticket_token_resolver->expects($this->once())->method('issue')
            ->with(1)->willReturn($token);

        $amount_without_fee = $amount_with_fee - ($raffle->getFirstRule()->fee * count($numbers));
        $lcs_ticket = [
            array_merge($this->buy_json[0], ['amount' => $amount_without_fee, 'lines_count' => count($numbers)])
        ];

        $this->buy_ticket_api->expects($this->once())->method('request')
            ->with([
                'tickets' => [
                    [
                        'token'  => $token,
                        'amount' => $amount_without_fee,
                        'ip'     => $user->last_ip,
                        'lines'  => array_map(function (int $number) {
                            return ['numbers' => [[$number]]];
                        }, $numbers)
                    ]
                ]
            ], $raffle->slug)->willReturn(
                $this->mock_lcs_response([
                    'lottery_tickets' => $lcs_ticket
                ])
            );

        # start_transaction
        $this->db->expects($this->once())->method('start_transaction');
        $this->db->expects($this->never())->method('rollback_transaction');
        $this->db->expects($this->once())->method('commit_transaction');

        $ticket = $this->get_ticket($lcs_ticket);
        $ticket->raffle = $raffle;

        $this->ticket_factory->expects($this->once())->method('create_from_lcs_ticket_data')
            ->with(1, $lcs_ticket[0], $raffle, $user, $numbers, $this->regular_balance_strategy)
            ->willReturn($ticket);

        $this->ticket_dao->expects($this->once())->method('store')->with($ticket);

        # disable_raffle_if_sold_out
        $this->raffleRepo
            ->expects($this->once())
            ->method('resetDrawLinesAndDisableSellWhenSoldOut')
            ->with($raffle->id);

        $this->db->expects($this->once())->method('commit_transaction');

        $this->service->purchase(1, $raffle->slug, 'type', $numbers, $user->id);
    }

    /** @test */
    public function purchaseDeterminePaymentMethodAndVerifyBalance_NoSufficientUserBalanceAndEnvIsProdOrDifferent_ThrowsBadMethodCallException(): void
    {
        // Except
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Your balance is too low to proceed. Please make <a href="/deposit/">a deposit</a>');

        // Given
        $numbers = range(1, 100);
        $raffle = $this->get_raffle(['is_sell_limitation_enabled' => true]);
        $raffle->is_sell_limitation_enabled = 1;
        $system = $this->getMockBuilder(System::class)->onlyMethods(['env'])->disableOriginalConstructor()->getMock();
        $this->system = $system;
        $raffle->whitelabel_raffle->is_bonus_balance_in_use = false;

        $this->raffle_dao
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $user = $this->get_user();
        $this->user_dao->method('get_user_by_id')->with($user->id)->willReturn($user);

        $this->number_validator->method('validate')->with($raffle, $numbers)->willReturn($numbers);

        /** @var RegularBalance|MockObject $regular_balance_strategy */
        $regular_balance_strategy = $this->createMock(RegularBalance::class);
        $regular_balance_strategy->method('source')->willReturn(RegularBalance::COLUMN_NAME);
        $regular_balance_strategy->method('hasSufficientBalanceToProcess')->willReturn(false);

        $this->balance_resolver->method('determinePaymentMethod')
            ->will($this->returnCallback(function (InteractsWithBalance $service, Raffle $raffle, WhitelabelUser $user, float $required_amount_in_user_currency) use ($regular_balance_strategy) {
                $service->setBalanceStrategy($regular_balance_strategy);
            }));
        $this->createService();

        // When
        $this->service->purchase(1, $raffle->slug, 'type', $numbers, $user->id);
    }

    /** @test */
    public function purchaseDeterminePaymentMethodAndVerifyBonusBalance_NoSufficientUserBonusBalanceAndEnvIsProdOrDifferent_ThrowsBadMethodCallException(): void
    {
        $expectedMessage = 'Your bonus balance is too low to proceed.';

        // Except
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->currency_calc->method('convert_to_any')->willReturn(12.00);

        // Given
        $numbers = range(1, 100);
        $raffle = $this->get_raffle(['is_sell_limitation_enabled' => true]);
        $raffle->is_sell_limitation_enabled = 1;

        $system = $this->getMockBuilder(System::class)->onlyMethods(['env'])->disableOriginalConstructor()->getMock();
        $this->system = $system;

        $raffle->whitelabel_raffle->is_bonus_balance_in_use = true;

        $this->raffle_dao
            ->method('get_by_slug_with_currency_and_rule')
            ->willReturn($raffle);

        $user = $this->get_user();
        $user->bonusBalance = 10.00;
        $this->user_dao->method('get_user_by_id')->with($user->id)->willReturn($user);

        $this->number_validator->method('validate')->with($raffle, $numbers)->willReturn($numbers);

        /** @var BonusBalance|MockObject $bonusBalanceStrategy */
        $bonusBalanceStrategy = $this->createMock(BonusBalance::class);
        $bonusBalanceStrategy->method('source')->willReturn(BonusBalance::COLUMN_NAME);
        $bonusBalanceStrategy->method('hasSufficientBalanceToProcess')->willReturn(false);

        $this->balance_resolver->method('determinePaymentMethod')
            ->will($this->returnCallback(function (InteractsWithBalance $service, Raffle $raffle, WhitelabelUser $user, float $required_amount_in_user_currency) use ($bonusBalanceStrategy) {
                $service->setBalanceStrategy($bonusBalanceStrategy);
            }));
        $this->createService();

        // When
        $this->service->purchase(1, $raffle->slug, 'type', $numbers, $user->id);
    }

    /** @test */
    public function buyTicketApiMock_Success(): void
    {
        $amount = 10;
        $token = Faker::forge()->numberBetween(1000, 100000);
        $ip = '127.0.18.1';

        $response = $this->buyTicketApiMock->request([
            'tickets' => [
                [
                    'token' => $token,
                    'amount' => $amount,
                    'ip' => $ip,
                    'lines' => array_map(function (int $number) {
                        return ['numbers' => [[$number]]];
                    }, [10,50,85])
                ]
            ]
        ], 'gg-world-raffle');

        $response_body = $response->get_body();
        $lcs_ticket = reset($response_body['lottery_tickets']);

        $this->assertTrue($response->is_success());

        $this->assertSame(Helpers_General::TICKET_STATUS_PENDING, $lcs_ticket['status']);
        $this->assertSame(3, $lcs_ticket['lines_count']);
        $this->assertSame($amount / 3, $lcs_ticket['amount_sale_point']);
        $this->assertSame('USD', $lcs_ticket['currency_code_sale_point']);
        $this->assertSame($token, $lcs_ticket['token']);
        $this->assertSame($amount, $lcs_ticket['amount']);
        $this->assertSame($ip, $lcs_ticket['ip']);
    }

    private function calculateTicketAmount(Raffle $raffle, array $ticket_line_numbers): float
    {
        return ($raffle->getFirstRule()->line_price + $raffle->getFirstRule()->fee) * sizeof($ticket_line_numbers);
    }

    private function createService(): void
    {
        $this->service = new Services_Raffle_Ticket(
            $this->taken_tickets_api,
            $this->buy_ticket_api,
            $this->user_dao,
            $this->raffle_dao,
            $this->ticket_dao,
            $this->ticket_token_resolver,
            $this->ticket_factory,
            $this->number_validator,
            $this->currency_calc,
            $this->db,
            $this->system,
            $this->balance_resolver,
            $this->raffleRepo,
            $this->fileLoggerService
        );
    }
}
