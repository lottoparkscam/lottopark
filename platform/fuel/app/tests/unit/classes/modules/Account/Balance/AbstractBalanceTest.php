<?php

namespace Tests\Unit\Classes\Modules\Account\Balance;

use Exception;
use Fuel\Core\Database_Expression;
use Helpers_General;
use InvalidArgumentException;
use Models\WhitelabelUser;
use Modules\Account\Balance\RegularBalance;
use Orm\RecordNotFound;
use Repositories\Orm\WhitelabelUserBalanceLogRepository;
use Repositories\Orm\WhitelabelUserRepository;
use RuntimeException;
use Services_Currency_Calc;
use Test_Unit;
use Tests\Fixtures\WhitelabelUserFixture;
use Wrappers\Db;

abstract class AbstractBalanceTest extends Test_Unit
{
    protected const SOURCE = 'balance';
    protected const PAYMENT_TYPE = Helpers_General::PAYMENT_TYPE_BALANCE;
    protected const AMOUNT_FIELD = 'amount';

    private WhitelabelUser $user_dao;
    private WhitelabelUser $user;
    private Db $db;
    private Services_Currency_Calc $currency_calc;
    private RegularBalance $service;
    private WhitelabelUserRepository $userRepository;
    private WhitelabelUserBalanceLogRepository $userBalanceLogRepository;

    private WhitelabelUserFixture $userFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->user_dao = $this->createStub(WhitelabelUser::class);
        $this->db = $this->createMock(Db::class);
        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
        $this->userRepository = $this->createMock(WhitelabelUserRepository::class);
        $this->userBalanceLogRepository = $this->createMock(WhitelabelUserBalanceLogRepository::class);

        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);

        $this->user = $this->get_user();

        $this->service = new RegularBalance(
            $this->db,
            $this->user_dao,
            $this->userRepository,
            $this->userBalanceLogRepository,
            $this->currency_calc
        );
    }

    /** @test */
    public function debit__valid_user_with_sufficient_balance_and_amount_greater_than_zero__converts_to_users_currency_and_debits(): void
    {
        // Given
        $currency = $this->user->currency;
        $amount = 10.0;
        $user_currency = $currency->code;
        $transaction_currency = 'ZYX'; # currency_code
        $user = $this->user;
        $user->{self::SOURCE} = 1000;
        $amount_after_convert = $amount / 2;

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($user);

        $this->currency_calc
            ->expects($this->once())
            ->method('convert_to_any')
            ->with($amount, $transaction_currency, $user_currency)
            ->willReturn($amount_after_convert);

        $expr_as_string = "NOW()";
        $expr = new Database_Expression($expr_as_string);

        $this->userRepository
            ->expects($this->once())
            ->method('updateFloatField')
            ->with($user->id, self::SOURCE, $amount_after_convert * -1, [
                'last_update' => $expr
            ]);

        $this->userBalanceLogRepository
            ->expects($this->once())
            ->method('addWhitelabelUserBalanceLog')
            ->with($user->id, 'Balance updated', $amount_after_convert * -1, $user->currency->code);

        $this->db
            ->expects($this->once())
            ->method('expr')
            ->with($expr_as_string)
            ->willReturn($expr);

        // When
        $this->service->debit($this->user->id, $amount, $transaction_currency);

        // Then

        $this->service->dispatch();
    }

    /**
     * @test
     * @dataProvider amount_is_not_greater_than_zero_provider
     * @param float $amount
     */
    public function debit__amount_is_not_greater_than_zero__throws_invalid_argument_exception(float $amount): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($this->user);

        // When
        $this->service->debit($this->user->id, $amount, 'USD');
    }

    /** @test */
    public function debit__many_calls_valid_user_with_sufficient_balance_and_amount_greater_than_zero__converts_to_users_currency_and_debits_by_one_db_call(): void
    {
        // Given
        $currency = $this->user->currency;
        $amount = 10.0;
        $user_currency = $currency->code;
        $transaction_currency = 'ZYX'; # currency_code
        $user = $this->user;
        $user->{self::SOURCE} = 1000;
        $amount_after_convert = $amount / 2;
        $service_invocations = 2;

        $expectedAmountAfterConvert = ($amount_after_convert * -1) * $service_invocations;

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($user);

        $this->currency_calc
            ->expects($this->exactly(2))
            ->method('convert_to_any')
            ->with($amount, $transaction_currency, $user_currency)
            ->willReturn($amount_after_convert);

        $expr_as_string = "NOW()";
        $expr = new Database_Expression($expr_as_string);

        $this->userRepository
            ->expects($this->once())
            ->method('updateFloatField')
            ->with($user->id, self::SOURCE, $expectedAmountAfterConvert, [
                'last_update' => $expr
            ]);

        $this->userBalanceLogRepository
            ->expects($this->once())
            ->method('addWhitelabelUserBalanceLog')
            ->with($user->id, 'Balance updated', $expectedAmountAfterConvert, $user->currency->code);

        $this->db
            ->expects($this->once())
            ->method('expr')
            ->with($expr_as_string)
            ->willReturn($expr);

        // When
        $this->service->debit($this->user->id, $amount, $transaction_currency);
        $this->service->debit($this->user->id, $amount, $transaction_currency);

        // Then
        $this->service->dispatch();
    }

    public function amount_is_not_greater_than_zero_provider(): array
    {
        return [
            'amount is equal zero' => [0],
            'amount is less than zero' => [-10],
        ];
    }

    /** @test */
    public function debit__user_not_exists__throws_record_not_found_exception(): void
    {
        // Except
        $this->expectException(RecordNotFound::class);

        // Given
        $user_id = rand(1, 10);
        $amount = 10;
        $currency_code = 'USD';

        $this->user_dao->
        expects($this->once())
            ->method('get_user_by_id')
            ->with($user_id)
            ->willThrowException(new RecordNotFound());

        // When
        $this->service->debit($user_id, $amount, $currency_code);
    }

    /** @test */
    public function debit__no_sufficient_balance__throws_runtime_exception(): void
    {
        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No sufficient balance to continue.');

        // Given
        $user_id = $this->user->id;
        $amount = 10;
        $currency_code = 'USD';
        $this->user->{self::SOURCE} = 0;

        $this->currency_calc->method('convert_to_any')->willReturn((float)$amount);

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($this->user);

        // When
        $this->service->debit($user_id, $amount, $currency_code);
    }

    # debitByTicket

    /** @test */
    public function debitByTicket__valid_user_with_sufficient_balance_and_amount_greater_than_zero__converts_to_users_currency_and_debits(): void
    {
        // Given
        $ticket = $this->get_ticket();
        $user = $ticket->user;
        $user->{self::SOURCE} = 1000;
        $transaction_amount = $ticket->transaction->amount;

        $expectedAmountAfterConvert = $transaction_amount * -1;

        $this->user_dao->
            expects($this->once())
            ->method('get_user_by_id')
            ->willReturn($ticket->user);

        $expr_as_string = "NOW()";
        $expr = new Database_Expression($expr_as_string);
        $this->db
            ->expects($this->once())
            ->method('expr')
            ->with($expr_as_string)
            ->willReturn($expr);

        $this->userRepository
            ->expects($this->once())
            ->method('updateFloatField')
            ->with($user->id, self::SOURCE, $expectedAmountAfterConvert, [
                'last_update' => $expr
            ]);

        $this->userBalanceLogRepository
            ->expects($this->once())
            ->method('addWhitelabelUserBalanceLog')
            ->with($user->id, 'Balance updated', $expectedAmountAfterConvert, $user->currency->code);

        // When
        $this->service->debitByTicket($ticket);

        // Then
        $this->service->dispatch();
    }

    /**
     * @test
     * @dataProvider amount_is_not_greater_than_zero_provider
     * @param float $amount
     */
    public function debitByTicket__amount_is_not_greater_than_zero__throws_invalid_argument_exception(float $amount): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        // Given
        $ticket = $this->get_ticket();
        $ticket->transaction->amount = $amount;

        // When
        $this->service->debitByTicket($ticket);
    }


    /** @test */
    public function debitByTicket__no_sufficient_balance__throws_runtime_exception(): void
    {
        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No sufficient balance to continue.');

        // Given
        $ticket = $this->get_ticket();
        $user = $ticket->user;
        $user->{self::SOURCE} = 0;

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($user);

        // When
        $this->service->debitByTicket($ticket);
    }

    # charge

    /** @test */
    public function charge__user_not_exists__throws_record_not_found_exception(): void
    {
        // Except
        $this->expectException(RecordNotFound::class);

        // Given
        $user_id = rand(1, 10);
        $amount = 10;
        $currency_code = 'USD';

        $this->user_dao->
        expects($this->once())
            ->method('get_user_by_id')
            ->with($user_id)
            ->willThrowException(new RecordNotFound());

        // When
        $this->service->increase($user_id, $amount, $currency_code);
    }

    /**
     * @test
     * @dataProvider amount_is_not_greater_than_zero_provider
     * @param float $amount
     */
    public function charge__amount_is_not_greater_than_zero__throws_invalid_argument_exception(float $amount): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($this->user);

        // When
        $this->service->increase($this->user->id, $amount, 'USD');
    }

    /** @test */
    public function charge__valid_user_and_amount__converts_to_users_currency_and_charges(): void
    {
        // Given
        $currency = $this->user->currency;
        $amount = 10.0;
        $user_currency = $currency->code;
        $transaction_currency = 'ZYX'; # currency_code
        $user = $this->user;
        $amount_after_convert = $amount / 2;

        $expr_as_string = "NOW()";
        $expr = new Database_Expression($expr_as_string);
        $this->db
            ->expects($this->once())
            ->method('expr')
            ->with($expr_as_string)
            ->willReturn($expr);

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($user);

        $this->currency_calc
            ->expects($this->once())
            ->method('convert_to_any')
            ->with($amount, $transaction_currency, $user_currency)
            ->willReturn($amount_after_convert);

        // When
        $this->service->increase($this->user->id, $amount, $transaction_currency);

        // Then
        $this->service->dispatch();
    }

    # hasSufficientBalanceToProcess

    /**
     * @test
     * @dataProvider hasSufficientBalanceToProcess__balance_is_at_least_equal_to_amount__provider
     * @param float $amount
     * @param float $balance
     */
    public function hasSufficientBalanceToProcess__balance_is_at_least_equal_to_amount__not_throws_exception(float $amount, float $balance): void
    {
        // Given
        $user_id = $this->user->id;
        $currency_code = 'USD';
        $this->user->{self::SOURCE} = $balance;

        $this->currency_calc->method('convert_to_any')->willReturn((float)$amount);

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn($this->user);

        // When
        $this->service->debit($user_id, $amount, $currency_code);
        $this->service->dispatch();

        // Then
        $this->assertTrue(true);
    }

    public function hasSufficientBalanceToProcess__balance_is_at_least_equal_to_amount__provider(): array
    {
        return [
            'amount is equal balance' => [10.0, 10.0],
            'amount is less than balance' => [10.0, 20.20],
        ];
    }

    # misc

    /** @test */
    public function toString__returns_payment_type(): void
    {
        // Given
        $expected = (string)self::PAYMENT_TYPE;

        // When & Then
        $this->assertSame($expected, (string)$this->service);
    }

    /** @test */
    public function source__returns_user_balance_field_name(): void
    {
        // Given
        $expected = self::SOURCE;

        // When & Then
        $this->assertSame($expected, $this->service->source());
    }

    /** @test */
    public function getTicketAmountToPayInUserCurrency__returns_proper_transaction_field(): void
    {
        // Given
        $ticket = $this->get_ticket();
        $ticket->transaction->amount = 1;
        $ticket->transaction->bonus_amount = 2;

        $expected = $ticket->transaction->{self::AMOUNT_FIELD};

        // When
        $amount = $this->service->getTicketAmountToPayInUserCurrency($ticket);

        // Then
        $this->assertEquals($expected, $amount);
    }

    /** @test */
    public function debit_UserInitiallyWithZeroBalanceIncreased_ShouldNotThrowAnError(): void
    {
        // Given user with 0 balance initially (ORM model)
        /** @var WhitelabelUser $user */
        $user = $this->userFixture->with('balance_0', 'currency.eur')->withIdsFor('id')->makeOne();

        // When account has been increased by 20 EUR
        $amount = 20.0;
        $this->user_dao->method('get_user_by_id')->willReturn($user);
        $this->currency_calc->method('convert_to_any')->willReturn($amount);
        $this->service->increase($user->id, $amount, 'EUR');

        // And attempted to debit by 20 EUR
        $this->service->debit($user->id, $amount, 'EUR');
        $this->service->dispatch();

        // Then Exception should not been thrown on dispatch call
        $this->assertTrue(true);
    }

    /** @test */
    public function debit_UserInitiallyWithZeroBalanceIncreasedAndDebitByTooBigAmount_ShouldThrowAnError(): void
    {
        // Exception that should have been thrown on dispatch call
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No sufficient balance to continue.');

        // Given user with 0 balance initially (ORM model)
        /** @var WhitelabelUser $user */
        $user = $this->userFixture->with('balance_0', 'currency.eur')->withIdsFor('id')->makeOne();

        // And amount 20.0 EUR to increase and 20.01 EUR to debit
        $amount = 20.0;
        $amountToDebit = 20.01;
        $this->user_dao->method('get_user_by_id')->willReturn($user);
        $this->currency_calc->method('convert_to_any')->willReturnOnConsecutiveCalls($amount, $amountToDebit);

        // When account has been increased by 20 EUR
        $this->service->increase($user->id, $amount, 'EUR');

        // And attempted to debit by 20.01 EUR
        $this->service->debit($user->id, $amountToDebit, 'EUR');
        $this->service->dispatch();
    }

    /** @test */
    public function charge_withInvalidUserId(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User does not have correct id! Cannot increase balance!');

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn(new WHitelabelUser());

        // When
        $this->service->increase($this->user->id, 10, 'USD');
    }

    /** @test */
    public function charge_withInvalidUser(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User does not have correct id! Cannot increase balance!');

        // When
        $this->service->increase(new WhitelabelUser(), 10, 'USD');
    }

    /** @test */
    public function debit_withInvalidUserId(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User does not have correct id! Cannot debit balance!');

        $this->user_dao
            ->method('get_user_by_id')
            ->willReturn(new WHitelabelUser());

        // When
        $this->service->debit($this->user->id, 10, 'USD');
    }

    /** @test */
    public function debit_withInvalidUser(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User does not have correct id! Cannot debit balance!');

        // When
        $this->service->debit(new WhitelabelUser(), 10, 'USD');
    }

    /** @test */
    public function debitAndCharge_withWalidUser(): void
    {
        $this->user_dao
            ->expects($this->never())
            ->method('get_user_by_id');

        // When
        $this->service->debit($this->user, 10, 'USD');
        $this->service->increase($this->user, 10, 'USD');
    }
}
