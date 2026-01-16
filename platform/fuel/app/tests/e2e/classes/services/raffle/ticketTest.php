<?php

use Models\Raffle;

class Tests_E2e_Classes_Services_Raffle_Ticket extends Test_Feature
{
    private const WHITELABEL_ID = 1; # todo: ST hardcoded!
    private const RAFFLE_SLUG = 'gg-world-raffle';
    private const RAFFLE_OPEN_TYPE = 'open';
    private const RAFFLE_CLOSED_TYPE = 'closed';

    /** @var Services_Currency_Calc */
    private $currency_calc;
    /** @var Raffle */
    private $raffle_dao;

    public function setUp(): void
    {
        parent::setUp();
        $this->skip_on_production_or_staging_env();
        $this->currency_calc = $this->container->get(Services_Currency_Calc::class);
        $this->raffle_dao = $this->container->get(Raffle::class);
    }

    /**
     * @param string $raffle_type
     *
     * @throws ErrorException
     * @throws DI\DependencyException
     * @throws DI\NotFoundException
     * @dataProvider type_provider
     */
    public function test_it_buys_tickets(string $raffle_type): void
    {
        $user = $this->get_user();
        $user_balance = $user->balance;

        $requested_lines = $this->get_random_available_numbers($raffle_type);

        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);
        $ticket = $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            $raffle_type,
            $requested_lines,
            $user->id
        );

        $expected_cost = $this->currency_calc->convert_to_any(
            $ticket->raffle->getFirstRule()->line_price + $ticket->raffle->getFirstRule()->fee,
            $ticket->raffle->getFirstRule()->currency->code,
            $user->currency->code
        ) * count($requested_lines);

        $this->assertNotEmpty($ticket->transaction);
        $this->assertNotEmpty($ticket->raffle);
        $lines = $ticket->lines;
        $this->assertNotEmpty($lines);
        $this->assertEquals(sizeof($requested_lines), sizeof($lines));
        foreach ($lines as $line) {
            $this->assertTrue(in_array($line->number, $requested_lines));
        }
        $user->reload();
        $this->assertNotSame($user_balance, $user->balance);
        $this->assertSame($user_balance - $expected_cost, $user->balance);
    }

    /** @test */
    public function purchase__exceeding_max_allowed_bets__throws_exception(): void
    {
        $user = $this->get_user();
        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG);
        $requested_lines = range(1, $raffle->max_bets + 1);

        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Too much bets provided. Lottery accept max %d numbers, %d given.', $raffle->max_bets, count($requested_lines)));

        $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            self::RAFFLE_CLOSED_TYPE,
            $requested_lines,
            $user->id
        );
    }

    /** @test */
    public function purchase__passing_non_numeric_value__throws_exception(): void
    {
        $user = $this->get_user();
        $requested_lines = [1, 'sdasd', 1.2];

        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Value %s is not valid number', 'sdasd'));

        $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            self::RAFFLE_CLOSED_TYPE,
            $requested_lines,
            $user->id
        );
    }

    /** @test */
    public function purchase__passing_number_out_of_raffle_range__throws_exception(): void
    {
        $user = $this->get_user();
        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG);
        [$min, $max] = $raffle->getFirstRule()->ranges[0];
        $requested_lines = [1, 2, 3, $max + 1];

        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Number must be in range %d - %d', $min, $max));

        $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            self::RAFFLE_CLOSED_TYPE,
            $requested_lines,
            $user->id
        );

        $requested_lines = [1, 2, 3, $min - 1];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Number must be in range %d - %d', $min, $max));

        $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            self::RAFFLE_CLOSED_TYPE,
            $requested_lines,
            $user->id
        );
    }

    public function test_it_throws_exception_when_lottery_is_disabled_in_wl_db(): void
    {
        $user = $this->get_user();
        $user->balance = 100000;
        $user->save();

        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG);
        $raffle->is_sell_limitation_enabled = true;
        $raffle->is_sell_enabled = false;
        $raffle->sell_open_dates = ['Mon 23:59', 'Tue 23:59', 'Wed 23:59', 'Thu 23:59', 'Fri 23:59', 'Sat 23:59', 'Sun 23:59'];
        $raffle->save();

        $dates = $raffle->sell_open_dates_objects;
        $date = reset($dates);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf('Ticket purchase is closed until %s', $date->format('Y-m-d H:i')));

        /** @var Services_Raffle_Ticket $service */
        $service = Container::get(Services_Raffle_Ticket::class);
        $service->purchase(
            self::WHITELABEL_ID,
            self::RAFFLE_SLUG,
            self::RAFFLE_CLOSED_TYPE,
            [1,2],
            $user->id
        );
    }

    public function type_provider(): array
    {
        return [
            [self::RAFFLE_CLOSED_TYPE],
        ];
    }

    /**
     * Returns any active user. If not results then exception will be thrown.
     *
     * @return WhitelabelUser
     * @throws ErrorException
     */
    private function get_user(): WhitelabelUser
    {
        $user = WhitelabelUser::dao()->push_criterias([
            new Model_Orm_Criteria_Where('is_active', true)
        ])->get_one();

        $user->balance = 100000;
        $user->save();
        return $user;
    }

    private function get_random_available_numbers(string $raffle_type = 'closed'): array
    {
        /** @var Services_Lcs_Raffle_Ticket_Free_Contract $taken_tickets_api */
        $result = Container::get(Services_Lcs_Raffle_Ticket_Free_Contract::class)->request(self::RAFFLE_SLUG, $raffle_type)->get_data();
        shuffle($result);
        return array_slice($result, 0, rand(1, 5));
    }
}
