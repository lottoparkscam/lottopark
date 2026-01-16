<?php

namespace Tests\Fixtures;

use Carbon\Carbon;
use Helpers_General;
use Models\Currency;
use Models\Lottery;
use Models\LotteryType;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Models\WhitelabelUserTicket;

final class WhitelabelUserTicketFixture extends AbstractFixture
{
    public const WHITELABEL_TRANSACTION = 'whitelabel_transaction';
    public const WHITELABEL_USER = 'whitelabel_user';
    public const CURRENCY = 'currency';
    public const PAID = 'paid';
    public const NOT_PAID = 'not_paid';

    public function getDefaults(): array
    {
        return [
            'tier' => 1,
            'is_synchronized' => $this->faker->boolean(),
            'payout' => $this->faker->boolean(),
            'is_insured' => $this->faker->boolean(),
            'has_ticket_scan' => $this->faker->boolean(),
            'paid' => true,
            'status' => Helpers_General::TICKET_STATUS_PENDING,
            'token' => $this->faker->numberBetween(10000, 9999999),
            'valid_to_draw' => $this->faker->dateTime('+6 months')->format('Y-m-d H:i:s'),
            'draw_date' => $this->faker->dateTime('+6 months')->format('Y-m-d H:i:s'),
            'date' => $this->faker->date(),
            'amount_local' => 0.0,
            'amount' => 0.0,
            'amount_usd' => 0.0,
            'cost_local' => 0.0,
            'cost' => 0.0,
            'cost_usd' => 0.0,
            'income_local' => 0.0,
            'income' => 0.0,
            'income_usd' => 0.0,
            'income_value' => 0.0,
            'income_type' => 0,
            'margin_local' => 0.0,
            'margin' => 0.0,
            'margin_usd' => 0.0,
            'margin_value' => 0.0,
            'bonus_amount_local' => 0.0,
            'bonus_amount_payment' => 0.0,
            'bonus_amount_usd' => 0.0,
            'bonus_amount' => 0.0,
            'bonus_amount_manager' => 0.0,
            'bonus_cost_local' => 0.0,
            'bonus_cost' => 0.0,
            'bonus_cost_usd' => 0.0,
            'bonus_cost_manager' => 0.0,
            'ip' => $this->faker->ipv4(),
            'line_count' => 0,
            'lottery_id' => 1,
            'whitelabel_id' => 1,
            'lottery_type_id' => 1,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelUserTicket::class;
    }

    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::WHITELABEL_USER => $this->reference('whitelabel_user', WhitelabelUserFixture::class),
            self::WHITELABEL_TRANSACTION => $this->reference(
                'whitelabel_transaction',
                WhitelabelTransactionFixture::class
            ),
            self::BASIC => $this->basic(),
            self::PAID => $this->paid(),
            self::NOT_PAID => $this->notPaid(),
        ];
    }

    private function basic(): callable
    {
        return function (WhitelabelUserTicket $whitelabelUserTicket, array $attributes = []) {
            if (empty($whitelabelUserTicket->currency)) {
                $whitelabelUserTicket->currency = $this->fixture(self::CURRENCY)->createOne(['code' =>
                    $this->faker->randomElement(['USD', 'EUR', 'PLN'])]);
                $whitelabelUserTicket->currency_id = $whitelabelUserTicket->currency->id;
            }

            if (empty($whitelabelUserTicket->whitelabelUser)) {
                $whitelabelUserTicket->whitelabelUser = $this->fixture(self::WHITELABEL_USER)->with('basic')->createOne();
                $whitelabelUserTicket->whitelabel_user_id = $whitelabelUserTicket->whitelabelUser->id;
            }

            if (empty($whitelabelUserTicket->whitelabelTransaction)) {
                $whitelabelUserTicket->whitelabelTransaction = $this->fixture(self::WHITELABEL_TRANSACTION)->with('basic')->createOne();
                $whitelabelUserTicket->whitelabelTransactionId = $whitelabelUserTicket->whitelabelTransaction->id;
            }
        };
    }

    private function paid(): callable
    {
        return function (WhitelabelUserTicket $whitelabelUserTicket, array $attributes = []) {
            $whitelabelUserTicket->paid = true;
        };
    }

    private function notPaid(): callable
    {
        return function (WhitelabelUserTicket $whitelabelUserTicket, array $attributes = []) {
            $whitelabelUserTicket->paid = false;
        };
    }

    public function withTransaction(WhitelabelTransaction $whitelabelTransaction)
    {
        $this->with(function (WhitelabelUserTicket $ticket) use ($whitelabelTransaction) {

            $whitelabelTransaction->amount = $ticket->amount;
            $whitelabelTransaction->amount_usd = $ticket->amount_usd;
            $whitelabelTransaction->amount_payment = $ticket->amount_payment;
            $whitelabelTransaction->amount_manager = $ticket->amount_manager;
            $whitelabelTransaction->cost_usd = $ticket->cost_usd;
            $whitelabelTransaction->cost = $ticket->cost;
            $whitelabelTransaction->cost_manager = $ticket->cost_manager;
            $whitelabelTransaction->income_usd = $ticket->income_usd;
            $whitelabelTransaction->income = $ticket->income;
            $whitelabelTransaction->income_manager = $ticket->income_manager;
            $whitelabelTransaction->margin_usd = $ticket->margin_usd;
            $whitelabelTransaction->margin = $ticket->margin;
            $whitelabelTransaction->margin_manager = $ticket->margin_manager;
            $whitelabelTransaction->bonus_amount_payment = $ticket->bonus_amount_payment = 0.00;
            $whitelabelTransaction->bonus_amount_usd = $ticket->bonus_amount_usd = 0.00;
            $whitelabelTransaction->bonus_amount = $ticket->bonus_amount = 0.00;
            $whitelabelTransaction->bonus_amount_manager = $ticket->bonus_amount_manager = 0.00;
            $whitelabelTransaction->date = $ticket->date;

            $ticket->whitelabelTransaction = $whitelabelTransaction;
        });
        return $this;
    }

    public function withWhitelabel(Whitelabel $wl): self
    {
        $this->with(function (WhitelabelUserTicket $ticket) use ($wl) {
            $ticket->whitelabel = $wl;
        });
        return $this;
    }

    public function withUser(WhitelabelUser $user): self
    {
        $this->with(function (WhitelabelUserTicket $ticket) use ($user) {
            $ticket->whitelabelUser = $user;
        });
        return $this;
    }

    public function withLottery(Lottery $lottery): self
    {
        $this->with(function (WhitelabelUserTicket $ticket) use ($lottery) {

            $lotteryType = LotteryType::find('first', [
                'where' => [
                    'lottery_id' => $lottery->id
                ]
            ]);

            $ticket->lottery = $lottery;
            $ticket->lottery_type_id = $lotteryType->id;
        });
        return $this;
    }

    public function withCurrency(Currency $currency): self
    {
        $this->with(function (WhitelabelUserTicket $ticket) use ($currency) {
            $ticket->currency = $currency;
        });
        return $this;
    }

    public function withDateTimeNow(): self
    {
        $this->with(function (WhitelabelUserTicket $ticket) {
            $ticket->date = Carbon::now();
        });
        return $this;
    }
}
