<?php

namespace Tests\Unit\Classes\Services\Raffle\Factory;

use Fuel\Core\Date;
use Helpers_General;
use Models\Whitelabel;
use Modules\Account\Balance\RegularBalance;
use Services_Currency_Calc;
use Services_Raffle_Factory_Ticket;
use Services_Raffle_Token_Transaction_Resolver;
use Services_Ticket_Calc_Amount;
use Services_Ticket_Calc_Margin;
use Services_Transaction_Calc_Amount;
use Services_Transaction_Calc_Margin;
use Test_Unit;

final class TicketTest extends Test_Unit
{
    private Services_Currency_Calc $currency_calc;
    private Services_Raffle_Token_Transaction_Resolver $transaction_token_resolver;
    private Services_Ticket_Calc_Amount $ticket_amount_calc;
    private Services_Transaction_Calc_Amount $transaction_amount_calc;
    private Services_Ticket_Calc_Margin $ticket_margin_calc;
    private Services_Transaction_Calc_Margin $transaction_margin_calc;
    private Whitelabel $whitelabel_dao;
    private array $valid_lcs_buy_response;
    private RegularBalance $regular_balance;

    public function setUp(): void
    {
        parent::setUp();

        $this->valid_lcs_buy_response = json_decode(file_get_contents(APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, 'tests\data\lcs\buy_ticket_response.json')), true);

        $this->currency_calc = $this->createMock(Services_Currency_Calc::class);
        $this->ticket_margin_calc = $this->createMock(Services_Ticket_Calc_Margin::class);
        $this->ticket_amount_calc = $this->createMock(Services_Ticket_Calc_Amount::class);
        $this->ticket_amount_calc->method('calculate')->will(
            $this->returnCallback(function ($ticket) {
                $ticket->amount = 42;
            })
        );
        $this->transaction_amount_calc = $this->createMock(Services_Transaction_Calc_Amount::class);
        $this->transaction_margin_calc = $this->createMock(Services_Transaction_Calc_Margin::class);
        $this->transaction_token_resolver = $this->createMock(Services_Raffle_Token_Transaction_Resolver::class);
        $this->whitelabel_dao = $this->createMock(Whitelabel::class);
        $this->regular_balance = $this->createMock(RegularBalance::class);
        $this->regular_balance->method('__toString')->willReturn((string)Helpers_General::PAYMENT_TYPE_BALANCE);
    }

    /** @test */
    public function createsTicket__FromLcsData(): void
    {
        $lcs_ticket_data = end($this->valid_lcs_buy_response);
        $raffle = $this->get_raffle();
        $wl = $raffle->whitelabel_raffle->whitelabel;
        $wl_id = $wl->id;
        $user = $this->get_user();
        $user->currency = $raffle->currency;
        $user->currency_id = $user->currency->id;
        $ticket_numbers = [1, 2, 3, 4];
        # total amount with fee * lines in rule currency
        $amount_with_fee = (float)($raffle->getFirstRule()->line_price + $raffle->getFirstRule()->fee) * count($ticket_numbers);
        //$amount_with_fee_usd = (float)($raffle->rule->line_price + $raffle->rule->fee) * count($ticket_numbers);

        # ticket_margin_calc
        $this->ticket_margin_calc->expects($this->once())
            ->method('calculate');

        # transaction_margin_calc
        $this->transaction_margin_calc->expects($this->once())
        ->method('calculate');

        # whitelabel dao
        $this->whitelabel_dao->expects($this->once())->method('get_by_id')->with($wl_id, ['currency'])->willReturn($wl);

        # ticket_amount_calc
        $this->ticket_amount_calc->expects($this->once())
            ->method('calculate');

        # transaction_amount_calc
        $this->transaction_amount_calc->expects($this->once())
            ->method('calculate');

        # currency_calc mock
        $user_line_price = $raffle->getFirstRule()->line_price + $raffle->getFirstRule()->fee;
        $this->currency_calc->expects($this->exactly(1))
            ->method('convert_to_any')
            ->withConsecutive(
                [$user_line_price, $raffle->getFirstRule()->currency->code, $user->currency->code],
            )
            ->willReturnOnConsecutiveCalls((float)$user_line_price);

        # token resolver mock
        $token = rand(1000, 10000);
        $this->transaction_token_resolver->expects($this->once())
            ->method('issue')
            ->with($wl_id)
            ->willReturn($token);

        $service = $this->createService();

        $ticket = $service->create_from_lcs_ticket_data(
            $wl_id,
            $lcs_ticket_data,
            $raffle,
            $user,
            $ticket_numbers,
            $this->regular_balance
        );

        # check, lines relations created from given numbers
        for ($c = 0; $c !== count($ticket_numbers); $c++) {
            $this->assertSame($ticket_numbers[$c], $ticket->lines[$c]->number);
            $this->assertSame($wl_id, $ticket->lines[$c]->whitelabel_id);
        }

        # check transaction
        $this->assertNotEmpty($ticket->transaction);
        $this->assertNotEmpty($ticket->transaction->currency);
        $this->assertSame((string)$ticket->transaction->amount, (string)$ticket->transaction->amount_payment);
        $this->assertSame($user->currency_id, $ticket->transaction->currency_id);
        $this->assertSame($user->currency_id, $ticket->transaction->payment_currency_id);

        # todo: verify why we have dupes in regular ticket, raffle tickets and transactions (income, manager etc)
        # https://ggintsoftware.slack.com/archives/GALAKBCBZ/p1600161898002800

        # todo amount usd transformation test

        $this->assertSame(Helpers_General::STATUS_TRANSACTION_APPROVED, $ticket->transaction->status);
        $this->assertSame($wl_id, $ticket->transaction->whitelabel_id);
        $this->assertSame(Date::forge(null, $user->timezone)->format('mysql'), $ticket->transaction->date->format('mysql'));
        $this->assertSame($token, $ticket->transaction->token);
        $this->assertSame(Helpers_General::TYPE_TRANSACTION_PURCHASE, $ticket->transaction->type);
        $this->assertSame($user->id, $ticket->transaction->whitelabel_user_id);
        $this->assertSame(Date::forge()->format('mysql'), $ticket->transaction->date_confirmed->format('mysql'));
        //$this->assertSame($amount_with_fee_usd, $ticket->transaction->amount_usd);

        # check ticket data
        $this->assertSame($user->id, $ticket->whitelabel_user_id);
        $this->assertSame($wl_id, $ticket->whitelabel_id);
        $this->assertSame($raffle->currency_id, $user->currency->id);
        $this->assertSame($raffle->id, $ticket->raffle_id);
        $this->assertSame($raffle->raffle_rule_id, $ticket->raffle_rule_id);
        $this->assertSame($amount_with_fee, $ticket->amount);
    }

    private function createService(): Services_Raffle_Factory_Ticket
    {
        return new Services_Raffle_Factory_Ticket(
            $this->currency_calc,
            $this->ticket_amount_calc,
            $this->transaction_amount_calc,
            $this->whitelabel_dao,
            $this->ticket_margin_calc,
            $this->transaction_margin_calc,
            $this->transaction_token_resolver
        );
    }
}
