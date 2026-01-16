<?php

namespace Tests\Fixtures\Raffle;

use Exception;
use Models\Raffle;
use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Helpers_General;
use Webmozart\Assert\Assert;
use Helpers_General as General;
use Tests\Fixtures\AbstractFixture;
use Tests\Fixtures\CurrencyFixture;
use Tests\Fixtures\WhitelabelFixture;
use Models\WhitelabelRaffleTicketLine;
use fixtures\Exceptions\MissingRelation;
use Tests\Fixtures\WhitelabelUserFixture;
use Models\WhitelabelRaffleTicket as Ticket;
use Tests\Fixtures\WhitelabelTransactionFixture;

final class RaffleTicketFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const WHITELABEL = 'whitelabel';
    public const WHITELABEL_LOTTOPARK = 'whitelabel_lottopark';
    public const USER = 'user';
    public const TRANSACTION = 'transaction';
    public const RAFFLE = 'raffle';
    public const RAFFLE_RULE = 'rule';
    public const ONE_RAFFLE = 'one_raffle';
    public const LINES = 'lines';
    public const DRAW = 'draw';

    // todo make states
    // @see https://trello.com/c/jg6eCOe2
    public const PENDING = 'pending';
    public const LOST = 'lost';
    public const WON = 'won';

    // Flags
    public const RANDOM = -1;
    public const MAX = -2;

    private ?Raffle $raffle = null;

    public function getDefaults(): array
    {
        $status = $this->faker->randomElement(
            [
                General::TICKET_STATUS_PENDING,
                General::TICKET_STATUS_WIN,
                General::TICKET_STATUS_NO_WINNINGS
            ]
        );
        $prize = 0.0;
        $won = $status === General::TICKET_STATUS_WIN;

        return [
            'uuid' => $this->faker->uuid(),
            'token' => (string)$this->faker->numberBetween(100000, 999999),
            'draw_date' => null,
            'status' => $status,
            'ip' => $this->faker->ipv4(),
            'ip_country_code' => $this->faker->countryCode(),
            'is_paid_out' => $this->faker->boolean(),
            'line_count' => $this->faker->numberBetween(1, 1000),

            'prize' => $won ? $prize = $this->faker->numberBetween(1, 1000) : 0.0,
            'prize_local' => $won ? $prize * 0.75 : 0.0,
            'prize_usd' => $won ? $prize * 0.85 : 0.0,
            'prize_manager' => $won ? $prize * 1.05 : 0.0,

            'amount' => $amount = $this->faker->numberBetween(1, 10),
            'amount_local' => $amount * 0.75,
            'amount_usd' => $amount * 0.75,
            'amount_payment' => $amount * 0.75,
            'amount_manager' => $amount * 0.75,

            'bonus_amount' => 0.0,
            'bonus_amount_local' => 0.0,
            'bonus_amount_usd' => 0.0,
            'bonus_amount_payment' => 0.0,
            'bonus_amount_manager' => 0.0,

            'cost_local' => 0.0,
            'cost_usd' => 0.0,
            'cost_manager' => 0.0,
            'cost' => 0.0,

            'bonus_cost_local' => 0.0,
            'bonus_cost_usd' => 0.0,
            'bonus_cost_manager' => 0.0,
            'bonus_cost' => 0.0,

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

            'created_at' => null,
            'updated_at' => null
        ];
    }

    public static function getClass(): string
    {
        return Ticket::class;
    }

    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::BASIC => $this->basic(),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::USER => $this->reference('user', WhitelabelUserFixture::class),
            self::TRANSACTION => $this->reference('transaction', WhitelabelTransactionFixture::class),
            self::RAFFLE => $this->reference('raffle', RaffleFixture::class),
            self::RAFFLE_RULE => $this->reference('rule', RaffleRuleFixture::class),
            self::LINES => $this->reference('lines', RaffleTicketLineFixture::class),
            self::ONE_RAFFLE => $this->oneRaffle(),
            self::DRAW => $this->reference('raffle_draw', RaffleDrawFixture::class),
        ];
    }

    private function basic(): callable
    {
        return function (Ticket $ticket, array $attributes = []): void {
            if (empty($ticket->currency)) {
                $ticket->currency = $this->fixture(self::CURRENCY)->makeOne();
            }

            if (empty($ticket->whitelabel)) {
                $ticket->whitelabel = $this->fixture(self::WHITELABEL)();
            }

            if (empty($ticket->user)) {
                $ticket->user = $this->fixture(self::USER)->with('basic')();
            }

            if (empty($ticket->transaction)) {
                // todo: create transaction as win or lost
                /** @var WhitelabelTransactionFixture $transactionFixture */
                $transactionFixture = $this->fixture(self::TRANSACTION);
                $ticket->transaction = $transactionFixture
                    ->withWhitelabel($ticket->whitelabel)
                    ->withUser($ticket->user)
                    ->with('basic')();
            }

            if (empty($ticket->raffle)) {
                $ticket->raffle = $this->fixture(self::RAFFLE)->with('basic')();
            }

            if (empty($ticket->rule)) {
                $ticket->rule = $ticket->raffle->getFirstRule();
            }
        };
    }

    /**
     * Allows to cache generated raffle and re-use for all makeMany calls
     * @return callable
     */
    private function oneRaffle(): callable
    {
        return function (Ticket $ticket, array $attributes = []): void {
            if (empty($this->raffle)) {
                $this->raffle = $this->fixture(self::RAFFLE)->with('basic')();
            }
            $ticket->raffle = $this->raffle;
            $ticket->rule = $ticket->raffle->getFirstRule();
        };
    }

    /**
     * Creates new ticket, basing on given line.
     * Missing mandatory fields, are replaced by basic state.
     *
     * @param WhitelabelRaffleTicketLine $line
     * @return $this
     */
    public function forLine(WhitelabelRaffleTicketLine $line): self
    {
        $this->with(
            function (Ticket $ticket, array $attributes = []) use ($line): void {
                $ticket->whitelabel = $line->whitelabel;
                if (!empty($line->prize)) {
                    $ticket->prize = $line->prize;
                    $ticket->prize_local = $line->prize_local;
                    $ticket->prize_manager = $line->prize_manager;
                    $ticket->prize_usd = $line->prize_usd;
                }
            },
            $this->basic(),
        );
        return $this;
    }

    public function forRaffle(Raffle $raffle): self
    {
        MissingRelation::verify($raffle, 'currency');

        $this->with(
            function (Ticket $ticket, array $attributes = []) use ($raffle): void {
                $ticket->raffle = $raffle;
                $ticket->currency = $raffle->currency;
            },
        );
        return $this;
    }

    /**
     * @param Raffle $raffle
     * @param string $status
     * @param int $ticketsCount
     * @param int $linesCount
     *
     * @return array<Ticket>
     */
    public function generateTickets(
        Raffle $raffle,
        string $status = self::PENDING,
        int $ticketsCount = self::RANDOM,
        int $linesCount = self::MAX
    ): array {
        Assert::inArray($status, [self::PENDING, self::WON, self::LOST]);
        if ($ticketsCount !== self::RANDOM || $linesCount !== self::MAX) {
            Assert::lessThanEq($ticketsCount, $linesCount, 'Tickets count must be less than lines count');
        }

        if ($linesCount === self::MAX) {
            $linesCount = $raffle->max_bets;
        }

        if ($ticketsCount !== self::RANDOM) {
            // todo add possibility to generate requested amount of the tickets
            // @see https://trello.com/c/jg6eCOe2
            throw new Exception('There is no ticket count mechanism implemented yet.');
        }

        $tickets = [];
        $numbers = range(1, $linesCount);

        /** @var RaffleTicketLineFixture $linesFixture */
        $linesFixture = $this->fixture(self::LINES);

        /** @var RaffleDrawFixture $drawFixture */
        $drawFixture = $this->fixture(self::DRAW);
        $draw = $drawFixture->forRaffle($raffle)->makeOne();

        for (;;) {
            if (empty($numbers)) {
                break;
            }
            /** @var Ticket $ticket */
            $ticket = $this->forRaffle($raffle)->with('basic')->makeOne(
                [
                    'status' => Helpers_General::TICKET_STATUS_PENDING
                ]
            );

            $ticket->currency = $raffle->currency;
            $ticket->draw = $draw;

            $lines = [];
            $nums = array_splice($numbers, 0, count($numbers)); // todo: split to many tickets
            foreach ($nums as $num) {
                $line = $linesFixture->forTicket($ticket)->with('basic')->makeOne(
                    [
                        'number' => $num,
                        'status' => Helpers_General::TICKET_STATUS_PENDING
                    ]
                );
                $lines[] = $line;
            }

            $ticket->lines = $lines;
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public function withWhitelabel(Whitelabel $whitelabel): self
    {
        $this->with(function (Ticket $ticket) use ($whitelabel) {
            $ticket->whitelabel = $whitelabel;
        });
        return $this;
    }

    public function withTransaction(WhitelabelTransaction $transaction): self
    {
        $this->with(function (Ticket $ticket) use ($transaction) {
            $ticket->transaction = $transaction;
        });

        return $this;
    }

    public function withCurrency(Currency $currency): self
    {
        $this->with(function (Ticket $ticket) use ($currency) {
            $ticket->currency = $currency;
        });

        return $this;
    }

    public function withRaffle(Raffle $raffle): self
    {
        $this->with(function (Ticket $ticket) use ($raffle) {
            $ticket->raffle = $raffle;
        });

        return $this;
    }

    public function withNumbers(array $numbers): self
    {
        $this->with(function (Ticket $ticket) use ($numbers) {
            $lines = [];

            foreach ($numbers as $number) {
                $lines[] = $this->fixture(self::LINES)->with('basic')->makeOne(
                    [
                        'number' => $number,
                        'status' => Helpers_General::TICKET_STATUS_PENDING
                    ]
                );
            }

            $ticket->lines = $lines;
        });

        return $this;
    }
}
