<?php

namespace Tests\Unit\Classes\Services\Ticket;

use Services_Currency_Calc;
use Services_Ticket_Calc_Wrappers_Cost;
use InvalidArgumentException;
use Services_Ticket_Calc_Margin;

final class MarginTest extends \Test_Unit
{
    private Services_Currency_Calc $currency_calc;
    private Services_Ticket_Calc_Wrappers_Cost $cost_calc;

    public function setUp(): void
    {
        parent::setUp();
        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
        $this->cost_calc = $this->createMock(Services_Ticket_Calc_Wrappers_Cost::class);
    }

    /** @test */
    public function calculatesAmounts(): void
    {
        $ticket = $this->get_ticket();
        $ticket->amount = $ticket->rule->line_price_with_fee * count($ticket->lines);
        $ticket->whitelabel->currency->code = 'PLN';
        $whitelabel_currency_code = $ticket->whitelabel->currency->code;
        $all_prizes_cost = 7400;
        $cost = ($all_prizes_cost / $ticket->rule->max_lines_per_draw) * count($ticket->lines);
        $amount = $ticket->amount;

        $this->cost_calc->expects($this->once())
            ->method('calculate_raffle_cost')
            ->with($ticket)
            ->willReturn($cost);

        $this->currency_calc->expects($this->exactly(6))
        ->method('convert_to_any')
            ->withConsecutive(
                [$cost, $ticket->raffle->currency->code, $ticket->user->currency->code],
                [$cost, $ticket->user->currency->code, 'USD'],
                [$cost, $ticket->user->currency->code, $whitelabel_currency_code],
                [$amount, $ticket->user->currency->code, 'USD'],
                [$amount, $ticket->user->currency->code, $ticket->raffle->currency->code],
                [$amount, $ticket->user->currency->code, $whitelabel_currency_code]
            )
            ->willReturnOnConsecutiveCalls(...[
                $this->calculate_currency($cost, 'USD'),
                $this->calculate_currency($cost, $ticket->raffle->currency->code),
                $this->calculate_currency($cost, $whitelabel_currency_code),
                $this->calculate_currency($amount, 'USD'),
                $this->calculate_currency($amount, $ticket->raffle->currency->code),
                $this->calculate_currency($amount, $whitelabel_currency_code),
            ]);

        $service = $this->createService();
        $service->calculate($ticket);
    }

    private function createService(): Services_Ticket_Calc_Margin
    {
        return new Services_Ticket_Calc_Margin(
            $this->currency_calc,
            $this->cost_calc
        );
    }

    public function whenNoLines_ThrowsException_(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket lines relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->lines);
        $service->calculate($ticket);
    }

    /** @test */
    public function whenNoRule_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->rule);
        $service->calculate($ticket);
    }

    /** @test */
    public function whenNoWl_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Whitelabel relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->whitelabel);
        $service->calculate($ticket);
    }
}
