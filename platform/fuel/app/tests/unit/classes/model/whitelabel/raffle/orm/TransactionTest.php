<?php

namespace Tests\Unit\Classes\Model\Whitelabel\Raffle\Orm;

use Exception;
use Test_Unit;
use Helpers_General;
use Models\Whitelabel;
use Models\PaymentMethod;
use Models\WhitelabelUserTicket;
use Models\WhitelabelTransaction;
use Models\WhitelabelPaymentMethod;

class TransactionTest extends Test_Unit
{
    /** @test */
    public function paid__SetsStatusAsApproved(): void
    {
        // Given
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_ticket = new WhitelabelUserTicket();
        $expectedStatus = 1;

        // When
        $transaction->pay();

        // Then
        $actual = $transaction->status;
        $this->assertSame($expectedStatus, $actual);
    }

    /** @test */
    public function paid_AlreadyPaid_ThrowsException(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Attempted to pay already approved transaction');

        // Given
        $transaction = new WhitelabelTransaction(['status' => 1]);

        // When
        $transaction->pay();
    }

    /** @test */
    public function paid_WlTicketsExists_ChangesItsStatusToPaid(): void
    {
        // Given
        $ticket1 = new WhitelabelUserTicket(['paid' => false]);
        $ticket2 = new WhitelabelUserTicket(['paid' => false]);

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_tickets = [
            $ticket1, $ticket2
        ];
        $expectedStatus = true;

        // When
        $transaction->pay();

        // Then
        foreach ($transaction->whitelabel_tickets as $ticket) {
            $actual = $ticket->paid;
            $this->assertSame($expectedStatus, $actual);
        }
    }

    /** @test */
    public function paymentFailed_WlTicketExists_ChangesItsStatusToNotPaid(): void
    {
        // Given
        $ticket1 = new WhitelabelUserTicket(['paid' => false]);
        $ticket2 = new WhitelabelUserTicket(['paid' => false]);
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_tickets = [$ticket1, $ticket2];
        $expectedStatus = false;

        // When
        $transaction->setStatusAsErrorWithTicket();

        // Then
        foreach ($transaction->whitelabel_tickets as $ticket) {
            $actual = $ticket->paid;
            $this->assertSame($expectedStatus, $actual);
        }
    }

    /** @test */
    public function paymentFailed_SetLastAttempt_AsCurrentDate(): void
    {
        // Given
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_ticket = new WhitelabelUserTicket();

        // When
        $transaction->setStatusAsErrorWithTicket();
        $actual = $transaction->payment_attempt_date;

        // Then
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function paymentFailed_AlreadyPaid_ThrowsException(): void
    {
        // Except
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Attempted to fail already approved transaction');

        // Given
        $transaction = new WhitelabelTransaction(['status' => 1]);

        // When
        $transaction->setStatusAsErrorWithTicket();
    }

    /** @test */
    public function paymentMethodSlug_RelationsExists_ReturnsSlug(): void
    {
        // Given
        $name = 'Uppercase name';
        $expectedSlug = 'uppercase-name';
        $transaction = new WhitelabelTransaction();
        $wlPaymentMethod = new WhitelabelPaymentMethod();
        $paymentMethod = new PaymentMethod(['name' => $name]);
        $wlPaymentMethod->payment_method = $paymentMethod;
        $transaction->whitelabel_payment_method = $wlPaymentMethod;

        // When
        $actual = $transaction->payment_method_slug;

        // Then
        $this->assertSame($expectedSlug, $actual);
    }

    /** @test */
    public function paymentMethodSlug_RelationsNotExists_ReturnsNull(): void
    {
        // Given
        $transaction = new WhitelabelTransaction();

        // When
        $actual = $transaction->payment_method_slug;

        // Then
        $this->assertNull($actual);
    }

    /** @test */
    public function prefixedToken_WhitelabelExists_AppendsItsPrefix(): void
    {
        // Given
        $wl = new Whitelabel();
        $wl->prefix = 'L';
        $token = 123;
        $transaction = new WhitelabelTransaction();
        $transaction->token = $token;
        $transaction->whitelabel = $wl;
        $transaction->type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        $expectedToken = 'LP123';

        // When
        $actual = $transaction->prefixed_token;

        // Then
        $this->assertSame($expectedToken, $actual);
    }

    /** @test */
    public function prefixedToken_WhitelabelNotExists_HasTwoCharsPrefix(): void
    {
        // Given
        $token = 123;
        $transaction = new WhitelabelTransaction();
        $transaction->token = $token;
        $transaction->type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        $expectedToken = 'P123';

        // When
        $actual = $transaction->prefixed_token;

        // Then
        $this->assertSame($expectedToken, $actual);
    }

    /** @test */
    public function prefixedToken_TypeIsNotPurchase_AppendsDPrefix(): void
    {
        // Given
        $token = 123;
        $transaction = new WhitelabelTransaction();
        $transaction->token = $token;
        $transaction->type = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
        $expectedToken = 'D123';

        // When
        $actual = $transaction->prefixed_token;

        // Then
        $this->assertSame($expectedToken, $actual);
    }

    /**
     * @test
     * @dataProvider paymentMethodsShouldIncreaseDataProvider
     * @param string $method
     */
    public function paymentMethods__ShouldIncreaseAttemptsCounter(string $method): void
    {
        // Given
        $transaction = new WhitelabelTransaction();

        // When & Then
        $this->assertSame(0, $transaction->payment_attempts_count);

        $transaction->$method();

        $this->assertSame(1, $transaction->payment_attempts_count);
    }

    public function paymentMethodsShouldIncreaseDataProvider(): array
    {
        return [
            'success' => ['pay'],
            'failure' => ['setStatusAsErrorWithTicket'],
            'attempt' => ['attemptPayment'],
        ];
    }

    /**
     * @test
     * @dataProvider setAdditionalDataDataProvider
     * @param $actualAdditionalData
     * @param $value
     * @param $expected
     */
    public function setAdditionalData__withProvidedData($actualAdditionalData, $value, $expected): void
    {
        // Given
        $transaction = new WhitelabelTransaction();
        $transaction->additional_data_json = $actualAdditionalData;
        $field = 'field1';

        // When
        $transaction->setAdditionalData($field, $value);

        // Then
        $this->assertSame($transaction->additional_data_json, $expected);
    }

    public function setAdditionalDataDataProvider(): array
    {
        return [
            'additional_data_json is null' => [
                null, 123, ['field1' => 123]
            ],
            'additional_data_json is array and edited field exists' => [
                ['field1' => 123], 1234, ['field1' => 1234]
            ],
            'additional_data_json is array and edited field not exists' => [
                ['field2' => 123], 1234, ['field2' => 123, 'field1' => 1234]
            ],
        ];
    }

    /**
     * @test
     * @param $actualAdditionalData
     * @param $expected
     * @dataProvider getAdditionalDataDataProvider
     * @return array
     */
    public function getAdditionalData__ReturnsAsArray($actualAdditionalData, $expected): void
    {
        // Given
        $transaction = new WhitelabelTransaction();
        $transaction->additional_data_json = $actualAdditionalData;

        // Then
        $actual = $transaction->getAdditionalData();
        $this->assertSame($actual, $expected);
    }

    public function getAdditionalDataDataProvider(): array
    {
        return [
            'additional_data_json is null' => [
                null, []
            ],
            'additional_data_json is defined array' => [
                ['field2' => 123], ['field2' => 123]
            ],
        ];
    }
}
