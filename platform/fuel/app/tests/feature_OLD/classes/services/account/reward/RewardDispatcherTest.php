<?php

namespace Feature\Services\Account\Reward;

use Container;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\WhitelabelRaffleTicket;
use Modules\Account\Reward\RewardDispatcher;
use Services_Lcs_Raffle_Ticket_Store_Contract;
use Test_Feature;

class RewardDispatcherTest extends Test_Feature
{
    /** @var RewardDispatcher */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $lcs_mock = $this->createMock(Services_Lcs_Raffle_Ticket_Store_Contract::class);
        $this->container->set(Services_Lcs_Raffle_Ticket_Store_Contract::class, $lcs_mock);
        $this->service = $this->container->get(RewardDispatcher::class);
    }

    /** @test */
    public function dispatch_ticket__all_lines_are_cash__marks_as_paid_out(): void
    {
        // Given
        $ticket = self::find_ticket(1); # gg-world has only cash prizes
        $expected_paid_out = true;

        // When
        $this->service->dispatchTicket($ticket);
        $this->service->dispatch();

        // Then
        $this->assertSame($expected_paid_out, $ticket->is_paid_out);
    }

    private static function find_ticket(int $raffle_id): ?WhitelabelRaffleTicket
    {
        /** @var WhitelabelRaffleTicket $ticket_dao */
        $ticket_dao = Container::get(WhitelabelRaffleTicket::class);
        $ticket = $ticket_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('lines'),
            new Model_Orm_Criteria_Where('raffle_id', $raffle_id) # because it have only cash prizes
        ])->find_one();

        if (empty($ticket)) {
            self::markTestSkipped('No GG World Ticket found');
        }

        $ticket->is_paid_out = false;
        return $ticket;
    }
}
