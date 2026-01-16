<?php

namespace Tests\Unit\Classes\Services\Ticket;

use Services_Currency_Calc;
use Models\WhitelabelRaffleTicketLine;
use Services_Ticket_Calc_Prize;
use InvalidArgumentException;

final class PrizeAmountTest extends \Test_Unit
{
    private Services_Currency_Calc $currency_calc;

    public function setUp(): void
    {
        parent::setUp();
        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
    }

    /** @test */
    public function calculatesPrizes(): void
    {
        $ticket = $this->get_ticket();
        $ticket->prize_local = 1;

        $ticket->whitelabel->currency->code = 'PLN';

        $whitelabel_currency_code = $ticket->whitelabel->currency->code;

        # currency calc
        $expected_invocations = count(
            array_filter($ticket->lines, function (WhitelabelRaffleTicketLine $line) {
                return !empty($line->raffle_prize);
            })
        );

        $params = [];
        $returns = [];

        for ($c = 0; $c !== count($ticket->lines); $c++) {
            if (empty($ticket->lines[$c]->raffle_prize)) { # relation
                continue;
            }

            $per_user = $ticket->lines[$c]->raffle_prize->per_user;
            $per_user_in_user_currency = $this->calculate_currency($per_user, $ticket->user->currency->code);

            # prize
            $params[] = [
                $per_user, $ticket->raffle->currency->code, $ticket->user->currency->code
            ];
            $returns[] = $per_user_in_user_currency;

            # prize_usd
            $params[] = [
                $per_user_in_user_currency, $ticket->user->currency->code, 'USD'
            ];
            $returns[] = $this->calculate_currency($per_user, 'USD');

            # prize_manager
            $params[] = [
                $per_user_in_user_currency, $ticket->user->currency->code, $whitelabel_currency_code
            ];
            $returns[] = $this->calculate_currency($per_user, $whitelabel_currency_code);
        }

        $this->currency_calc->expects($this->exactly($expected_invocations * 3))
            ->method('convert_to_any')
            ->withConsecutive(...$params)
            ->willReturnOnConsecutiveCalls(...$returns);

        $service = $this->createService();
        $previous_ticket_prize = $ticket->prize;
        $previous_ticket_prize_manager = $ticket->prize_manager;
        $previous_ticket_prize_usd = $ticket->prize_usd;
        $service->calculate($ticket);

        $prize_sum = 0;
        $prize_usd_sum = 0;
        $prize_local_sum = 0;
        $prize_manager_sum = 0;

        # I assumed that there is no prize then no prizes are recalculated.
        foreach ($ticket->lines as $line) {
            if (empty($line->raffle_prize)) {
                $this->assertEmpty($line->raffle_prize);
                $this->assertEquals(0.0, $line->prize);
                $this->assertEquals(0.0, $line->prize_usd);
                $this->assertEquals(0.0, $line->prize_local);
                $this->assertEquals(0.0, $line->prize_manager);
                continue;
            }

            $prize_sum += ($prize = $this->calculate_currency($line->raffle_prize->per_user, $ticket->user->currency->code));
            $prize_usd_sum += ($prize_usd = $this->calculate_currency($line->raffle_prize->per_user, 'USD'));
            $prize_local_sum += ($prize_local = $this->calculate_currency($line->raffle_prize->per_user, $ticket->raffle->currency->code));
            $prize_manager_sum += ($prize_manager = $this->calculate_currency($line->raffle_prize->per_user, $whitelabel_currency_code));

            $this->assertNotNull($line->raffle_prize);
            $this->assertSame($prize, $line->prize);
            $this->assertSame($prize_usd, $line->prize_usd);
            $this->assertSame($prize_local, $line->prize_local);
            $this->assertSame($prize_manager, $line->prize_manager);
        }

        # check sums
        $this->assertSame((float)$prize_sum, $ticket->prize - $previous_ticket_prize);
        $this->assertSame((float)$prize_usd_sum, $ticket->prize_usd - $previous_ticket_prize_usd);
        $this->assertSame((float)$prize_manager_sum, $ticket->prize_manager - $previous_ticket_prize_manager);
    }

    private function createService(): Services_Ticket_Calc_Prize
    {
        return new Services_Ticket_Calc_Prize(
            $this->currency_calc
        );
    }

    /** @test */
    public function whenPrizeIsZero_DoNothing(): void
    {
        $service = $this->createService();
        $ticket = $this->get_ticket();
        $ticket->prize = 0.0;
        $service->calculate($ticket);
        $this->assertSame(0.0, $ticket->prize);
    }

    public function test_it_throws_exception_when_no_lines(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ticket lines relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->lines);
        $service->calculate($ticket);
    }

    public function test_it_throws_exception_when_no_rule(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->rule);
        $service->calculate($ticket);
    }

    public function test_it_throws_exception_when_no_wl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Whitelabel relation can not be empty.');
        $service = $this->createService();
        $ticket = $this->get_ticket();
        unset($ticket->whitelabel);
        $service->calculate($ticket);
    }
}
