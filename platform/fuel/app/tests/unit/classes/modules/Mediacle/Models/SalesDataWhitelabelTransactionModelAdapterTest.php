<?php

namespace Unit\Modules\Mediacle\Models;

use Carbon\Carbon;
use Fuel\Core\Date;
use Helpers_General;
use Models\Whitelabel;
use Models\WhitelabelUserTicket;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Models\WhitelabelRaffleTicket;
use Models\WhitelabelAff;
use Models\WhitelabelCampaign;
use Models\WhitelabelPromoCode;
use Models\WhitelabelUserAff;
use Models\WhitelabelUserPromoCode;
use Modules\Mediacle\Models\SalesDataWhitelabelTransactionModelAdapter;
use Test_Unit;

class SalesDataWhitelabelTransactionModelAdapterTest extends Test_Unit
{
    /** @test */
    public function getPlayerId(): void
    {
        // Given
        $token = 'token';
        $expected = "DJU$token";
        $transaction = new WhitelabelTransaction();
        $transaction->user = new WhitelabelUser();
        $transaction->user->token = $token;
        $whitelabel = new Whitelabel(['prefix' => 'DJ']);
        $transaction->whitelabel = $whitelabel;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getPlayerId();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getBrand__ReturnsWhitelabelName(): void
    {
        // Given
        $expected = 'some name';
        $whitelabel = new Whitelabel(['name' => $expected]);
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel = $whitelabel;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getBrand();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTransactionDate__ReturnsDataInExpectedFormat(): void
    {
        // Given
        $date = new Carbon();
        $expected = $date->format('Y-m-d H:i:s');
        $transaction = new WhitelabelTransaction();
        $transaction->date = (new Date($date->getTimestamp(), $date->getTimezone()))->format('mysql');

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getTransactionDate();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getDeposits_TransactionIsDepositType_ReturnsAmountInUsd(): void
    {
        // Given
        $expected = 10.5;
        $transaction = new WhitelabelTransaction();
        $transaction->type = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
        $transaction->amount_usd = $expected;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getDeposits();

        // Then
        $this->assertTrue($transaction->is_deposit_type);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getDeposits_TransactionIsNotDepositType_ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $transaction = new WhitelabelTransaction();
        $transaction->type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        $transaction->amount_usd = $expected;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getDeposits();

        // Then
        $this->assertFalse($transaction->is_deposit_type);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getBets_TransactionIsDepositType_ReturnsAmountInUsd(): void
    {
        // Given
        $expected = 10.5;
        $transaction = new WhitelabelTransaction();
        $transaction->type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        $transaction->amount_usd = $expected;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getBets();

        // Then
        $this->assertTrue($transaction->is_purchase_type);
        $this->assertSame($expected, $actual);
    }

    private function testProperty(string $property, string $field, $expected): void
    {
        // Given
        $transaction = new WhitelabelTransaction([
            $field => $expected
        ]);

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $methodName = 'get' . ucfirst($property);
        $actual = $adapter->{$methodName}();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getCosts(): void
    {
        $this->testProperty('costs', 'cost_usd', 0.0);
    }

    /** @test */
    public function getPaymentCosts(): void
    {
        $this->testProperty('PaymentCosts', 'payment_cost_usd', 7.7);
    }

    /** @test */
    public function getRoyalties(): void
    {
        $this->testProperty('royalties', 'margin_usd', 7.7);
    }

    /** @test */
    public function getBets_TransactionIsNotDepositType_ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $transaction = new WhitelabelTransaction();
        $transaction->type = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
        $transaction->amount_usd = $expected;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getBets();

        // Then
        $this->assertFalse($transaction->is_purchase_type);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTimeStamp__UniquenessAcrossSingleResponse(): void
    {
        // Given
        $time1 = sprintf("%u", microtime(true) * 100000000000);
        usleep(100);
        $time2 = sprintf("%u", microtime(true) * 100000000000);
        $transaction1 = new WhitelabelTransaction();
        $transaction2 = new WhitelabelTransaction();
        $transaction1->published_at_timestamp = $time1;
        $transaction2->published_at_timestamp = $time2;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction1);
        $actual1 = $adapter->getTimeStamp();
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction2);
        $actual2 = $adapter->getTimeStamp();

        // Then - we want to make sure that every generation of the timestamp gives unique value
        // NOTE: we mocked saveOrThrow and due to that it will be unique despite that normally it should save and return the same
        $this->assertNotSame($actual1, $actual2);
    }

    /** @test */
    public function getTimeStamp__ConsistentAcrossCalls(): void
    {
        // Given
        $time = sprintf("%u", microtime(true) * 1000000000);
        $transaction = new WhitelabelTransaction();
        $transaction->published_at_timestamp = $time;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual1 = $adapter->getTimeStamp();
        $transaction = new WhitelabelTransaction(['published_at_timestamp' => $actual1]);
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual2 = $adapter->getTimeStamp();

        // Then
        $this->assertSame($actual1, $actual2);
    }

    /** @test */
    public function getCurrencyRateToGbp__ReturnsNull(): void
    {
        // Given
        $expected = null;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getCurrencyRateToGbp();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getFirstDepositDate_ValueDefined_ReturnsFirstDepositValue(): void
    {
        // Given
        $date = '2020-01-01 14:00:12';
        $expected = $date;
        $user = new WhitelabelUser();
        $user->first_deposit = $date;
        $transaction = new WhitelabelTransaction();
        $transaction->user = $user;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getFirstDepositDate();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getFirstDepositDate_ValueNotDefined_ReturnsNull(): void
    {
        // Given
        $expected = 0.0;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getChargeBacks();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getChargeBacks__ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $user = new WhitelabelUser();
        $transaction = new WhitelabelTransaction();
        $transaction->user = $user;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getChargeBacks();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getReleasedBonuses__ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $user = new WhitelabelUser();
        $transaction = new WhitelabelTransaction();
        $transaction->user = $user;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getReleasedBonuses();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getRevenues_NoTickets_ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getRevenues();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getRevenues_TicketsExists_ReturnsIncomeUsd(): void
    {
        // Given
        $expected = 40.0;

        $wlTickets = [
            new WhitelabelUserTicket(['income_usd' => 10]),
            new WhitelabelUserTicket(['income_usd' => 10]),
            new WhitelabelUserTicket(['income_usd' => 10]),
        ];

        $raffleTicket = new WhitelabelRaffleTicket(['income_usd' => 10]);

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_tickets = $wlTickets;
        $transaction->whitelabel_raffle_ticket = $raffleTicket;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getRevenues();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTrackingId_UserRelatedDataExists_ReturnsAffToken(): void
    {
        // Given
        $expected = '12sdaa';

        $wlAff = new WhitelabelAff();
        $wlAff->token = $expected;

        $wlUserAff = new WhitelabelUserAff();
        $wlUserAff->whitelabel_aff = $wlAff;

        $user = new WhitelabelUser();
        $user->whitelabel_user_aff = $wlUserAff;

        $transaction = new WhitelabelTransaction();
        $transaction->user = $user;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getTrackingId();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getTrackingId_UserRelatedDataNotExists_ReturnsNull(): void
    {
        // Given
        $expected = null;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getTrackingId();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getWins_NoWlAndRaffleTickets_ReturnsZero(): void
    {
        // Given
        $expected = 0.0;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getWins();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getWins_WlAndRaffleTicketsExists_ReturnsSumOfPrizeUsds(): void
    {
        // Given
        $expected = 40.0;

        $wlTickets = [
            new WhitelabelUserTicket(['prize_usd' => 10]),
            new WhitelabelUserTicket(['prize_usd' => 10]),
            new WhitelabelUserTicket(['prize_usd' => 10]),
        ];

        $raffleTicket = new WhitelabelRaffleTicket(['prize_usd' => 10]);

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_tickets = $wlTickets;
        $transaction->whitelabel_raffle_ticket = $raffleTicket;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getWins();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPromoCode_NotExists_ReturnsNull(): void
    {
        // Given
        $expected = null;
        $transaction = new WhitelabelTransaction();

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getPromoCode();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getPromoCode_Exists_ReturnsPromoToken(): void
    {
        // Given
        $expected = 'token123';
        $transaction = new WhitelabelTransaction();
        $wlPromoCode = new WhitelabelPromoCode();
        $wlUserPromoCode = new WhitelabelUserPromoCode();
        $wlUserPromoCode->whitelabel_promo_code = $wlPromoCode;
        $wlUserPromoCode->whitelabel_promo_code->whitelabel_campaign = new WhitelabelCampaign(['token' => $expected, 'type' => 2]);
        $user = new WhitelabelUser();
        $user->whitelabel_user_promo_code = $wlUserPromoCode;
        $transaction->user = $user;

        // When
        $adapter = new SalesDataWhitelabelTransactionModelAdapter($transaction);
        $actual = $adapter->getPromoCode();

        // Then
        $this->assertSame($expected, $actual);
    }
}
