<?php

use Fuel\Core\Database_Query_Builder;

/**
 * Test ticket synchronization.
 * NOTE: it will not send data to LCS, it will use mock data instead.
 * Otherwise we would need additional sale point at LCS.
 *
 * Date: 2019-06-21
 * Time: 09:09:06
 */
final class Tests_Feature_Task_Lotterycentralserver_Chain_Synchronize_Tickets extends Test_Feature
{

    /**
     * Test retrieval of unsynchronized tickets from database.
     * NOTE: it will only check sql and php correctness.
     *
     * @return void
     */
    public function test_unsynchronized_retrieval(): void
    {
        $unsynchronized_tickets_with_lines = Model_Whitelabel_User_Ticket::unsychronized_for_lottery_with_lines_lcs(1, '2020-01-01'); // id is not important

        // TODO: {Vordis 2020-05-19 10:59:01} stub
        $this->assertInstanceOf(Database_Query_Builder::class, $unsynchronized_tickets_with_lines);
    }

    /**
     * Test update unsynchronized tickets to synchronized in database.
     * NOTE: it will only check sql and php correctness.
     *
     * @return void
     */
    public function test_update_to_synchronized(): void
    {
        $update_result =
            Model_Whitelabel_User_Ticket::update_to_synchronized_for_lottery(1); // it will be rolled back

        $this->assertTrue(is_integer($update_result));
    }
}
