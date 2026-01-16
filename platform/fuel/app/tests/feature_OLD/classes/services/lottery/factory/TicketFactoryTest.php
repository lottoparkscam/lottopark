<?php


use Services\Lottery\Factory\TicketFactory;

class TicketFactoryTest extends Test_Feature
{
    /** @var TicketFactory */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = $this->container->get(TicketFactory::class);
    }

    /** @test */
    public function create_random_ticket(): void
    {
        $wl_id = 1;
        $user_id = 1;
        $slug = 'powerball';
        $ticket_count = 5;
        $lines_count = 1;

        $result = $this->factory->create_bonus_ticket(
            $wl_id,
            $user_id,
            $slug,
            $ticket_count,
            $lines_count
        );

        foreach ($result as $ticket) {

            # args assigned properly
            $this->assertSame($ticket['whitelabel_user_id'], $user_id);
            $this->assertSame($ticket['whitelabel_id'], $wl_id);
            $this->assertSame($ticket['lottery_id'], '1');
            $this->assertSame($ticket['status'], Helpers_General::TICKET_STATUS_PENDING);

            # lines
            $this->assertSame($ticket['line_count'], $lines_count);
            $this->assertSame(
                count(Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $ticket['id'])),
                $lines_count
            );

            $this->assertNotEmpty($ticket['valid_to_draw']);
            $this->assertNotEmpty($ticket['draw_date']);

            # no transaction
            $this->assertFalse(isset($ticket['whitelabel_transaction_id']));
        }

        $this->assertTrue(true);

        $this->assertSame($ticket_count * $lines_count, Model_Whitelabel_User_Ticket_Line::count());
        $this->assertSame($ticket_count, Model_Whitelabel_User_Ticket::count());
    }
}
