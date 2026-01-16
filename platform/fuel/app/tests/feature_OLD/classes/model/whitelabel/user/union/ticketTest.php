<?php

use Orm\RecordNotFound;

/**
 * Helper test for testing ORM.
 */
class Tests_Feature_Model_Whitelabel_User_Union_Ticket extends Test_Feature
{
    /** @var Model_Whitelabel_User_Union_Ticket */
    private $ticket_dao;
    /** @var WhitelabelUser */
    private $user_dao;

    /** @var Model_Whitelabel_User_Union_Ticket[]  */
    private $tickets = [];

    /** @var WhitelabelUser */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticket_dao = Container::get(Model_Whitelabel_User_Union_Ticket::class);
        $this->user_dao = Container::get(WhitelabelUser::class);
        $this->user = $this->user_dao->get_one();
        $this->tickets = $this->ticket_dao->get_results();
        if (empty($this->tickets)) {
            $this->skip_due_no_expected_data_retrieved();
        }
    }

    public function test_it_returns_tickets(): void
    {
        $results = $this->ticket_dao->for_user(1)->get_results();

        foreach ($results as $ticket) {
            $fields = [
              'id',
              'whitelabel_id',
              'whitelabel_user_id',
              'whitelabel_transaction_id',
              'lottery_id',
              'raffle_id',
              'token',
              'status',
              'currency_id',
              'draw_date',
              'ticket_amount',
              'prize',
              'ip',
              'is_paid_out',
              'created_at',
            ];
            foreach ($fields as $field) {
                $this->assertTrue(isset($ticket[$field]), sprintf('Field %s does not exists', $field));
            }
        }
    }

    public function test_it_returns_only_paid_pending_tickets(): void
    {
        $results = $this->ticket_dao->for_user($this->user->id)->only_pending()->get_results();

        if (empty($results)) {
            $this->skip_due_no_expected_data_retrieved();
        }

        foreach ($results as $ticket) {
            $this->assertTrue($ticket->paid);
        }
    }

    public function test_it_returns_only_provided_user_tickets(): void
    {
        $results = $this->ticket_dao->for_user($this->user->id)->only_pending()->get_results();

        if (empty($results)) {
            $this->skip_due_no_expected_data_retrieved();
        }

        foreach ($results as $ticket) {
            $this->assertTrue($ticket->paid);
            $this->assertSame($ticket->whitelabel_user_id, $this->user->id);
        }
    }

    public function test_it_returns_count(): void
    {
        $count = $this->ticket_dao->for_user($this->user->id)->get_count();
        $this->assertIsInt($count);
    }

    public function test_it_returns_past_tickets_count(): void
    {
        $count = $this->ticket_dao->only_past()->filter_status(Helpers_General::TICKET_STATUS_WIN)->for_user(1)->get_count();
        $this->assertIsInt($count);
    }

    public function test_exists_returns_true_if_exists(): void
    {
        $ticket = reset($this->tickets);
        $this->assertTrue($this->ticket_dao->exists($ticket->id));
    }

    public function test_exists_returns_false_if_not_exists(): void
    {
        $this->assertFalse($this->ticket_dao->exists(-1));
    }

    public function test_it_gets_one(): void
    {
        $ticket = reset($this->tickets);
        $this->assertNotEmpty($this->ticket_dao->add_where('id', $ticket->id)->get_one());
    }

    public function test_it_reloads(): void
    {
        $ticket = reset($this->tickets);
        $result = $this->ticket_dao->add_where('id', $ticket->id)->get_one();
        $this->assertNotEmpty($result);
        $ticket->reload();
        $this->assertSame($result->to_array(), $ticket->to_array());
    }

    public function test_it_throws_exception_on_gets_one(): void
    {
        $this->expectException(RecordNotFound::class);
        $this->assertNotEmpty($this->ticket_dao->add_where('id', rand(10000, 100000))->get_one());
    }

    public function test_it_paginates(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function test_it_orders(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
