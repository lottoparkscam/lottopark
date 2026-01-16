<?php

namespace Tests\Unit\Classes\Services\Ticket;

use Services_Ticket_Calc_Amount;
use Services_Currency_Calc;
use Models\WhitelabelRaffleTicketLine;
use InvalidArgumentException;

final class AmountTest extends \Test_Unit
{
    private Services_Currency_Calc $currency_calc;

    public function setUp(): void
    {
        parent::setUp();
        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
    }

    /** @test */
    public function calculatesAmounts(): void
    {
        $ticket = $this->get_ticket();

        $ticket->whitelabel->currency->code = 'PLN';

        $whitelabel_currency_code = $ticket->whitelabel->currency->code;

        # currency calc
        $expected_invocations = count(
            array_filter($ticket->lines, function (WhitelabelRaffleTicketLine $line) {
                return $line->amount > 0;
            })
        );

        $params = [];
        $returns = [];

        for ($c = 0; $c !== count($ticket->lines); $c++) {
            if (!$ticket->lines[$c]->amount) {
                continue;
            }
            # amount_usd
            $params[] = [
                $ticket->lines[$c]->amount, $ticket->user->currency->code, 'USD'
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->amount, 'USD');

            # amount_local
            $params[] = [
                $ticket->lines[$c]->amount, $ticket->user->currency->code, $ticket->raffle->currency->code
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->amount, $ticket->raffle->currency->code);

            # amount_manager
            $params[] = [
                $ticket->lines[$c]->amount, $ticket->user->currency->code, $whitelabel_currency_code
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->amount, $whitelabel_currency_code);

            # amount_payment
            //$params[] = [
             //   $ticket->lines[$c]->amount, $ticket->user->currency->code, $ticket->user->currency->code
           // ];
           // $returns[] = $this->calculate_currency($ticket->lines[$c]->amount, $ticket->user->currency->code);
        }

        $this->currency_calc->expects($this->exactly($expected_invocations * 3))
            ->method('convert_to_any')
            ->withConsecutive(...$params)
            ->willReturnOnConsecutiveCalls(...$returns);

        $service = $this->createService();
        $service->calculate($ticket);

        $amount_usd_sum = 0;
        $amount_local_sum = 0;
        $amount_manager_sum = 0;
        $amount_payment_sum = 0;

        # I assumed that there is no amount then no amounts are recalculated.
        foreach ($ticket->lines as $line) {
            if (!$line->amount) {
                $this->assertEquals(0.0, $line->amount);
                $this->assertEquals(0.0, $line->amount_usd);
                $this->assertEquals(0.0, $line->amount_local);
                $this->assertEquals(0.0, $line->amount_manager);
                $this->assertEquals(0.0, $line->amount_payment);
                continue;
            }

            $amount_usd_sum += ($amount_usd = $this->calculate_currency($line->amount, 'USD'));
            $amount_local_sum += ($amount_local = $this->calculate_currency($line->amount, $ticket->raffle->currency->code));
            $amount_manager_sum += ($amount_manager = $this->calculate_currency($line->amount, $whitelabel_currency_code));
            $amount_payment_sum += ($amount_payment = $line->amount);

            $this->assertNotNull($line->amount);
            $this->assertSame($amount_usd, $line->amount_usd);
            $this->assertSame($amount_local, $line->amount_local);
            $this->assertSame($amount_manager, $line->amount_manager);
            $this->assertSame($amount_payment, $line->amount_payment);
        }

        # check sums
        $this->assertSame((float)$amount_usd_sum, $ticket->amount_usd);
        $this->assertSame((float)$amount_local_sum, $ticket->amount_local);
        $this->assertSame((float)$amount_manager_sum, $ticket->amount_manager);

        # check $ticket->amount
        $this->assertSame((string)$ticket->amount_payment, (string)$ticket->amount);
    }

    private function createService(): Services_Ticket_Calc_Amount
    {
        return new Services_Ticket_Calc_Amount(
            $this->currency_calc
        );
    }

    /** @test */
    public function calculateAmount_amountIsZero_ticketAmountGreaterOrEqualToFirstLineAmount(): void
    {
        $service = $this->createService();
        $ticket = $this->get_ticket();
        $ticket->amount = 0.0;
        $service->calculate($ticket);
        $this->assertGreaterThanOrEqual((string)$ticket->lines[0]->amount, (string)$ticket->amount);
    }

    /** @test */
    public function whenNoLines__ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket lines relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->lines);
        $service->calculate($ticket);
    }

    /** @test */
    public function whenNoRule__ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->rule);
        $service->calculate($ticket);
    }

    /** @test */
    public function whenNoWl__ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Whitelabel relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->whitelabel);
        $service->calculate($ticket);
    }

    /** @test */
    public function calculatesBonusAmounts(): void
    {
        $ticket = $this->get_ticket([], true);

        //Change amount to bonus_amount
        $ticket->bonus_amount = $ticket->amount;
        $ticket->amount = 0;

        $ticket->whitelabel->currency->code = 'PLN';

        $whitelabel_currency_code = $ticket->whitelabel->currency->code;

        # currency calc
        $expected_invocations = count(
            array_filter($ticket->lines, function (WhitelabelRaffleTicketLine $line) {
                return $line->bonus_amount > 0;
            })
        );

        $params = [];
        $returns = [];

        for ($c = 0; $c !== count($ticket->lines); $c++) {
            if (!$ticket->lines[$c]->bonus_amount) {
                continue;
            }
            # amount_usd
            $params[] = [
                $ticket->lines[$c]->bonus_amount, $ticket->user->currency->code, 'USD'
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->bonus_amount, 'USD');

            # amount_local
            $params[] = [
                $ticket->lines[$c]->bonus_amount, $ticket->user->currency->code, $ticket->raffle->currency->code
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->bonus_amount, $ticket->raffle->currency->code);

            # amount_manager
            $params[] = [
                $ticket->lines[$c]->bonus_amount, $ticket->user->currency->code, $whitelabel_currency_code
            ];
            $returns[] = $this->calculate_currency($ticket->lines[$c]->bonus_amount, $whitelabel_currency_code);
        }

        $this->currency_calc->expects($this->exactly($expected_invocations * 3))
            ->method('convert_to_any')
            ->withConsecutive(...$params)
            ->willReturnOnConsecutiveCalls(...$returns);

        $service = $this->createService();
        $service->calculate_bonus($ticket);

        $bonus_amount_usd_sum = 0;
        $bonus_amount_local_sum = 0;
        $bonus_amount_manager_sum = 0;
        $bonus_amount_payment_sum = 0;

        # I assumed that there is no amount then no amounts are recalculated.
        foreach ($ticket->lines as $line) {
            if (!$line->bonus_amount) {
                $this->assertEquals(0.0, $line->bonus_amount);
                $this->assertEquals(0.0, $line->bonus_amount_usd);
                $this->assertEquals(0.0, $line->bonus_amount_local);
                $this->assertEquals(0.0, $line->bonus_amount_manager);
                $this->assertEquals(0.0, $line->bonus_amount_payment);
                continue;
            }

            $bonus_amount_usd_sum += ($bonus_amount_usd = $this->calculate_currency($line->bonus_amount, 'USD'));
            $bonus_amount_local_sum += ($bonus_amount_local = $this->calculate_currency($line->bonus_amount, $ticket->raffle->currency->code));
            $bonus_amount_manager_sum += ($bonus_amount_manager = $this->calculate_currency($line->bonus_amount, $whitelabel_currency_code));
            $bonus_amount_payment_sum += ($bonus_amount_payment = $line->bonus_amount);

            $this->assertNotNull($line->bonus_amount);
            $this->assertSame($bonus_amount_usd, $line->bonus_amount_usd);
            $this->assertSame($bonus_amount_local, $line->bonus_amount_local);
            $this->assertSame($bonus_amount_manager, $line->bonus_amount_manager);
            $this->assertSame($bonus_amount_payment, $line->bonus_amount_payment);
        }

        # check sums
        $this->assertSame((float)$bonus_amount_usd_sum, $ticket->bonus_amount_usd);
        $this->assertSame((float)$bonus_amount_local_sum, $ticket->bonus_amount_local);
        $this->assertSame((float)$bonus_amount_manager_sum, $ticket->bonus_amount_manager);

        # check $ticket->amount
        $this->assertSame((string)$ticket->bonus_amount_payment, (string)$ticket->bonus_amount);
    }
}
