<?php

use Fuel\Core\DB;
use Fuel\Core\Config;
use Models\WhitelabelUser;
use Models\WhitelabelRaffleTicket;

/**
 * Class Tests_E2e_Services_Raffle_Sync_Ticket
 *
 * Steps to reproduce easily:
 * 1. php artisan migrate:fresh --seed && php artisan receipt:run --test on LCS
 * 2. cd platform && php oil r migration:fresh --drop-cache --seed --update-lotteries && php artisan receipt:run
 * 3. php oil r generate raffle-slug
 * 4. job:draw-scheduler && job:prize-scheduler on lcs
 * 5. Run this test.
 *
 * Improvements todo:
 * 1. Add calculating sum of paid out prizes in user currency.
 * 2. Add calculating sum of paid out prizes with user group (reduction by percent).
 * 3. Preparation sql doesn't recognize what currency id should be in ticket & user.
 * 4. Missing recognition that reward is in kind, then we do not transfer it anywhere.
 */
class Tests_E2e_Services_Raffle_Sync_Ticket_Prize extends Test_Feature
{
    protected const RAFFLE_SLUG = 'faireum-raffle';
    protected const RAFFLE_TYPE = 'closed';

    /** Services_Raffle_Sync_Ticket */
    protected $ticket_sync_service;
    /** Services_Raffle_Sync_Draw */
    protected $draw_sync_service;
    /** @var Raffle */
    protected $raffle_dao;
    /** @var Raffle */
    protected $raffle;
    /** @var WhitelabelRaffleTicket */
    protected $ticket_dao;
    /** @var RaffleDraw */
    protected $draw_dao;
    /** @var Services_Lcs_Raffle_Ticket_Get_Contract */
    protected $lcs_ticket_get;
    /** @var WhitelabelUser */
    protected $user_dao;
    /** @var Services_Currency_Calc */
    protected $currency_calc;
    /** @var mixed|WhitelabelRaffleTicketLine */
    private $ticket_line_dao;

    public function setUp(): void
    {
        parent::setUp();

        $this->skip_on_production_or_staging_env();

        if (Config::get('mock_lcs')) {
            $this->skip_due_no_expected_data_retrieved('Test has been skipped due LCS is mocked and this case is not covered.');
        }

        $this->raffle_dao = $this->container->get(Raffle::class);
        $this->raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG);

        DB::query(/** @lang MySql */'
        UPDATE whitelabel_user SET balance = 0, currency_id = 1, prize_payout_whitelabel_user_group_id = null;
        UPDATE whitelabel_raffle_ticket SET status = 0, raffle_draw_id = null, draw_date = null, is_paid_out = false WHERE raffle_id = :raffle_id;
        DELETE whitelabel_raffle_ticket_line;
        DELETE whitelabel_raffle_ticket;
        ')->bind('raffle_id', $this->raffle->id)->execute();

        $this->ticket_sync_service = $this->container->get(Services_Raffle_Sync_Ticket::class);
        $this->draw_sync_service = $this->container->get(Services_Raffle_Sync_Draw::class);
        $this->ticket_dao = $this->container->get(WhitelabelRaffleTicket::class);
        $this->ticket_line_dao = $this->container->get(WhitelabelRaffleTicketLine::class);
        $this->draw_dao = $this->container->get(RaffleDraw::class);
        $this->lcs_ticket_get = $this->container->get(Services_Lcs_Raffle_Ticket_Get_Contract::class);
        $this->user_dao = $this->container->get(WhitelabelUser::class);
        $this->currency_calc = $this->container->get(Services_Currency_Calc::class);
    }

    /**
     * It calls API to get last 10 draws,
     * iterates throw WL DB and checks given draw_no exists,
     * if no then new draw with prizes is created,
     * raffle data is updated,
     * All errors and successes are store in LOG db.
     *
     * @throws Throwable
     */
    public function test_it_synchronizes_data(): void
    {
        $unsynchronized_tickets_before = $this->ticket_dao->get_all_unsynchronized_tickets(self::RAFFLE_SLUG);
        $this->draw_sync_service->synchronize($this->raffle->slug, self::RAFFLE_TYPE);

        if (empty($unsynchronized_tickets_before)) {
            $this->skip_due_no_expected_data_retrieved();
        }

        $this->assertSame(
            1000,
            $this->ticket_line_dao->push_criterias([
                new Model_Orm_Criteria_With_Relation('ticket.raffle'),
                new Model_Orm_Criteria_With_Relation('raffle_prize.tier.tier_prize_in_kind'),

                new Model_Orm_Criteria_Where('ticket.raffle.slug', $this->raffle->slug),
                new Model_Orm_Criteria_Where('ticket.status', Helpers_General::TICKET_STATUS_PENDING),
            ])->getCount()
        );

        $ids = [];
        $lines_count = 0;

        foreach ($unsynchronized_tickets_before as $ticket) {
            $ids [] = $ticket->id;
            $lines_count += count($ticket->lines);
            $this->assertSame((int)Helpers_General::TICKET_STATUS_PENDING, (int)$ticket->status);
            $this->assertEmpty($ticket->draw_date);
            $this->assertEmpty($ticket->raffle_draw_id);
            $this->assertFalse((bool)$ticket->is_paid_out);
        }

        $this->ticket_sync_service->synchronize(self::RAFFLE_SLUG, self::RAFFLE_TYPE);

        $synchronized_tickets = $this->ticket_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('raffle'),
            new Model_Orm_Criteria_With_Relation('lines'),
            new Model_Orm_Criteria_With_Relation('currency'),

            new Model_Orm_Criteria_Where('id', $ids, 'in'),
        ])->get_results();

        $this->assertNotEmpty($synchronized_tickets);

        foreach ($synchronized_tickets as $ticket) {
            $this->assertNotSame((int)Helpers_General::TICKET_STATUS_PENDING, (int)$ticket->status);
            $this->assertNotEmpty($ticket->draw_date);
            $this->assertNotNull($ticket->raffle_draw_id);
            $this->assertTrue((bool)$ticket->is_paid_out);
            foreach ($ticket->lines as $line) {
                if ($line->status === Helpers_General::TICKET_STATUS_WIN) {
                    $this->assertNotEmpty($line->raffle_prize);
                    $this->assertNotEmpty($line->raffle_prize->raffle_draw_id);
                }
            }
        }

        # test dispatched reward are covered
        $lines_won_count = 1000;
        $ticket = reset($unsynchronized_tickets_before);
        $last_draw = $this->draw_dao->order_by('id', 'desc')->get_one();
        $this->assertSame($lines_won_count, $last_draw->lines_won_count);
        $this->assertSame($ticket->raffle_id, $last_draw->raffle_id);
        $this->assertSame($ticket->raffle_rule_id, $last_draw->raffle_rule_id);
        $this->assertSame(count($unsynchronized_tickets_before), $last_draw->tickets_count);

        $payload = ['uuids' => array_map(function (WhitelabelRaffleTicket $ticket) {
            return $ticket->uuid;
        }, $unsynchronized_tickets_before)];

        $lcs_tickets = $this->lcs_ticket_get->request($payload, self::RAFFLE_SLUG)->get_body();

        $this->assertSame(count($lcs_tickets), count($unsynchronized_tickets_before));

        $user_ids = [];

        foreach ($synchronized_tickets as $ticket) {
            $lcs = array_filter($lcs_tickets, function (array $data) use ($ticket) {
                return $data['uuid'] === $ticket->uuid;
            });
            $this->assertNotEmpty($lcs);
            $ticket_lcs = reset($lcs);
            $this->assertSame($ticket_lcs['uuid'], $ticket->uuid);
            $this->assertSame((int)$ticket_lcs['token'], $ticket->token);
            $this->assertSame($ticket_lcs['status'], $ticket->status);
            $this->assertSame((float)$ticket_lcs['amount'], $ticket->amount_local);
            $this->assertSame($ticket_lcs['lines_count'], count($ticket->lines));
            $this->assertSame((float)$ticket_lcs['prize'], $ticket->prize_local);
            $this->assertSame($ticket_lcs['currency_code'], $ticket->user->currency->code);
            $this->assertSame((bool)$ticket_lcs['is_paid_out'], $ticket->is_paid_out);
            $this->assertSame($ticket_lcs['draw_date'], $ticket->draw_date->format('mysql'));
            $user_ids[] = $ticket->whitelabel_user_id;
        }

        $users = $this->user_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('group'),
            new Model_Orm_Criteria_With_Relation('currency'),
            new Model_Orm_Criteria_Where('id', $user_ids, 'in')
        ])->get_results();

        $balances_sums_in_raffle_currency = 0.0;

        $balances = array_map(function (WhitelabelUser $user) use (&$balances_sums_in_raffle_currency, $last_draw) {
            $balances_sums_in_raffle_currency += $user->balance;
            return [
                'email' => $user->email,
                'balance' => $user->balance,
                'bonus_balance' => $user->bonus_balance,
                'currency' => $user->currency->code,
            ];
        }, $users);

        $this->assertNotSame(
            $last_draw->prize_total,
            $balances_sums_in_raffle_currency
        );

        # tier matches winers * winning tickets per prize gives us the results below
        $this->assertSame(1269, Model_Whitelabel_User_Ticket::count());
        $this->assertSame(1269, Model_Whitelabel_User_Ticket_Line::count());
    }
}
