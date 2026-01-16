<?php

final class Tests_Feature_Model_Whitelabel_Raffle_Orm_Ticket_Line extends Test_Feature
{
    const RAFFLE_ID = 3;

    /** @var WhitelabelRaffleTicketLine */
    private $ticket_lines_dao;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticket_lines_dao = Container::get(WhitelabelRaffleTicketLine::class);
    }

    /** @test */
    public function get_all_unsynchronized_lines__there_are_unsycned_lines__returns_lines(): void
    {
        $lines = $this->ticket_lines_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('ticket.raffle'),

            new Model_Orm_Criteria_Where('ticket.raffle.id', self::RAFFLE_ID),
            new Model_Orm_Criteria_Where('ticket.status', Helpers_General::TICKET_STATUS_PENDING),
            new Model_Orm_Criteria_Where('ticket.is_paid_out', false),
            new Model_Orm_Criteria_Where('ticket.raffle_draw_id', null),

            new Model_Orm_Criteria_Where('status', Helpers_General::TICKET_STATUS_PENDING),
        ])->get_results(5);

        if (empty($lines)) {
            $this->skip_due_no_expected_data_retrieved('No tickets found, test skipped.');
        }

        foreach ($lines as $line) {
            $this->assertNotEmpty($line->ticket->raffle);
            $this->assertSame(self::RAFFLE_ID, $line->ticket->raffle_id);
            $this->assertSame((int)Helpers_General::TICKET_STATUS_PENDING, (int)$line->ticket->status);
            $this->assertTrue(empty($line->ticket->is_paid_out));
            $this->assertEmpty($line->ticket->raffle_draw_id);
            $this->assertSame((int)Helpers_General::TICKET_STATUS_PENDING, (int)$line->status);
        }
    }
}
