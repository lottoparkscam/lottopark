<?php

namespace Tests\Fixtures\Raffle;

use Helpers_General;
use Models\WhitelabelRaffleTicket;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\WhitelabelFixture;
use Models\WhitelabelRaffleTicketLine;
use Tests\Fixtures\Raffle\RafflePrizeFixture;

final class RaffleTicketLineFixture extends AbstractFixture
{
    public const TICKET = 'ticket';
    public const WHITELABEL = 'whitelabel';
    public const PRIZE = 'raffle_prize';

    public function getDefaults(): array
    {
        return [
            'number' => $this->faker->numberBetween(1, 10000),
            'status' => $status = $this->faker->randomElement(
                [
                    Helpers_General::TICKET_STATUS_NO_WINNINGS,
                    Helpers_General::TICKET_STATUS_PENDING,
                    Helpers_General::TICKET_STATUS_WIN,
                ]
            ),

            'prize' => $status === Helpers_General::TICKET_STATUS_WIN ? $this->faker->numberBetween(1, 100000) : 0.0,
            'prize_local' => 0.0,
            'prize_usd' => 0.0,
            'prize_manager' => 0.0,

            'amount' => 0.0, // todo
            'amount_local' => 0.0,
            'amount_usd' => 0.0,
            'amount_payment' => 0.0,
            'amount_manager' => 0.0,

            'bonus_amount' => 0.0, // todo
            'bonus_amount_local' => 0.0,
            'bonus_amount_usd' => 0.0,
            'bonus_amount_payment' => 0.0,
            'bonus_amount_manager' => 0.0,

            'cost_local' => 0.0, // todo
            'cost_usd' => 0.0,
            'cost_manager' => 0.0,
            'cost' => 0.0,

            'margin_value' => 0.0,
            'margin_local' => 0.0,
            'margin_usd' => 0.0,
            'margin' => 0.0,
            'margin_manager' => 0.0,

            'income_local' => 0.0,
            'income_usd' => 0.0,
            'income' => 0.0,
            'income_value' => 0.0,
            'income_manager' => 0.0,
            'income_type' => 0.0,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelRaffleTicketLine::class;
    }

    public function getStates(): array
    {
        return [
            self::TICKET => $this->reference('ticket', RaffleTicketFixture::class),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::BASIC => $this->basic(),
            self::PRIZE => $this->reference('raffle_prize', RafflePrizeFixture::class),
        ];
    }

    private function basic(): callable
    {
        return function (WhitelabelRaffleTicketLine $line, array $attributes = []): void {
            if (empty($line->ticket)) {
                /** @var RaffleTicketFixture $fixture */
                $fixture = $this->fixture(self::TICKET);
                $line->ticket = $fixture->forLine($line)->makeOne();
            }

            if (empty($line->whitelabel)) {
                $line->whitelabel = $this->fixture(self::WHITELABEL)->makeOne();
            }

            $shouldAddRafflePrize = empty($line->raffle_prize) && !(empty($line->ticket->raffle));
            if ($shouldAddRafflePrize) {
                /** @var RafflePrizeFixture $rafflePrizeFixture */
                $rafflePrizeFixture = $this->fixture(self::PRIZE);
                $line->raffle_prize = $rafflePrizeFixture->forRaffle($line->ticket->raffle)->makeOne();
            }
        };
    }

    public function forTicket(WhitelabelRaffleTicket $ticket): self
    {
        $this->with(
            function (WhitelabelRaffleTicketLine $line, array $attributes = []) use ($ticket): void {
                $line->ticket = $ticket;
                $line->whitelabel = $ticket->whitelabel;
            },
            $this->basic()
        );
        return $this;
    }
}
