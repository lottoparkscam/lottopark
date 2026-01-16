<?php

final class Dummy_LCS_Payout extends Task_Task
{
    public function run(): void
    {
        // do nothing, ommit this part of functionallity at this point of time.
        // we could and probably should check send object here by doing some assertions
        // or just do it in separate test, to not complicate things
    }
}

/**
 * Test ticket prize data update.
 * NOTE: it will not send data to LCS, it will use mock data instead.
 * Otherwise we would need additional sale point at LCS.
 *
 * Date: 2019-06-21
 * Time: 09:09:06
 */
final class Tests_Feature_Task_Lotterycentralserver_Chain_Update_Tickets extends Test_Feature
{
    protected $models_to_rewind = [
        Model_Whitelabel_Transaction::class,
        Model_Whitelabel_User_Ticket::class,
        Model_Whitelabel_User_Ticket_Line::class,
        Model_Whitelabel_User_Ticket_Slip::class,
        Model_Lcs_Ticket::class,
        Model_Lottery_Draw::class,
        Model_Lottery_Prize_Data::class,
    ];

    /**
     * Data created by factories for test.
     * @var array
     */
    private $tickets_data = [];

    /**
     * Data created by factories for test.
     * @var array
     */
    private $draw_data;

    /**
     * lottery.
     * @var Model_Lottery
     */
    private $lottery;

    /**
     * fetch_tickets_result.
     * @var Task_Result
     */
    private $fetch_tickets_result;

    private function update_ticket_attributes(array $ticket_data, array $lcs_tickets): void
    {
        $this->tickets_data = array_merge_recursive($this->tickets_data, $ticket_data);
        // add tickets to attribute
        $this->fetch_tickets_result->set_data([
            'lottery_tickets' => array_merge($this->fetch_tickets_result->get_data()['lottery_tickets'], $lcs_tickets),
        ]);
    }

    private function generate_lost_tickets(Model_Lottery $lottery, Model_Whitelabel_User $user, Model_Lottery_Type $lottery_type, int $count = 1, int $lines_per_ticket = 10): void
    { // TODO: {Vordis 2019-09-30 15:18:37} this should be extracted from here
        $ticket_data = Test_Factory_Whitelabel_Transaction::create([
            'whitelabel_id' => $user->whitelabel_id,
            'whitelabel_user_id' => $user->id,
            'currency_id' => $user->currency_id,
        ])
            ->with(Test_Factory_Whitelabel_User_Ticket::class, [
                'whitelabel_id' => $user->whitelabel_id,
                'lottery_id' => $lottery->id,
                'lottery_type_id' => $lottery_type->id,
                'valid_to_draw' => $lottery->last_date_local,
                'line_price' => $lottery->price,
                'line_count' => $lines_per_ticket,
                'is_synchronized' => true,
            ], $count)
            ->with_multiple(
                [
                    Test_Factory_Whitelabel_User_Ticket_Slip::class => [],
                    Test_Factory_Whitelabel_User_Ticket_Line::class => [
                        'values' => [
                            'lottery_type' => $lottery_type,
                        ],
                        'count' => $lines_per_ticket
                    ],
                ],
                true
            )
            ->get_result();

        $lcs_tickets = [];
        for ($j = 0; $j < $count; $j++) {
            $ticket_data['lcs_ticket'] =
                Test_Factory_Lcs_Ticket::create([
                    'whitelabel_user_ticket_slip_id' => $ticket_data['whitelabel_user_ticket_slip'][$j]['id']
                ])->get_result()['lcs_ticket'];

            // generate dummy lcs response
            $get_ticket_data = function () use (&$ticket_data): array {
                $lcs_ticket = current($ticket_data['lcs_ticket']);
                next($ticket_data['lcs_ticket']);
                return [
                    'uuid' => $lcs_ticket['uuid'],
                ];
            };
            // TODO: {Vordis 2019-09-23 13:43:23} numbers are not used in check, the only thing important is status and prize
            $get_lines = function () use (&$ticket_data, $j): array {
                $lines = [];
                for ($i = 0; $i < 10; $i++) {
                    $whitelotto_line = $ticket_data['whitelabel_user_ticket_line'][$i * $j];
                    $lines[] = Test_Mock_Lcs_Ticket_Line::mock([
                        'numbers' => explode(',', $whitelotto_line['numbers']),
                    ]);
                }
                return $lines;
            };
            // TODO: {Vordis 2019-09-23 13:38:52} mocking has to be similiar to factories and allow for usage of closures and dynamic number of items
            $lcs_tickets[] = Test_Mock_Lcs_Ticket::mock($get_ticket_data(), $get_lines());
        }

        $this->update_ticket_attributes($ticket_data, $lcs_tickets);
    }

    public function setUp(): void
    { // TODO: {Vordis 2019-09-23 17:09:06} fast and dirty if I have more time I will abstract it
        parent::setUp(); // parent logic - start transaction etc
        // prepare data for tests.
        // TODO: {Vordis 2019-09-20 10:25:31} simplification - we reuse values from seeders. Tests should be fully autonomous - they mustn't depend on anything but themselves.
        // generate tickets for already existing lottery
        // fetch models
        $user = Model_Whitelabel_User::find_one_by('email', 'test@user.loc');
        $this->lottery = $lottery = Model_Lottery::find_one_by('slug', 'lotto-zambia');
        $lottery->last_numbers = '14,27,18,16,11,20';
        $lottery->last_date_local = '2019-09-20 09:28:19';
        $lottery->save();
        $lottery_type = Model_Lottery_Type::last_for_lottery($lottery->id);

        // generate draw and prizes
        $this->draw_data = Test_Factory_Lottery_Draw::create([
            'lottery' => $lottery,
            'lottery_type' => $lottery_type,
        ])
            ->get_result();
        $type_datum = Model_Lottery_Type_Data::tail_for_lottery_type($lottery_type->id, 4);
        for ($i = 0; $i < 3; $i++) {
            Test_Factory_Lottery_Prize_Data::create([
                'lottery_draw_id' => $this->draw_data['lottery_draw'][0]['id'],
                'lottery_type_data_id' => $type_datum[$i]->id,
            ]);
        }
        Test_Factory_Lottery_Prize_Data::create([
            'lottery_draw_id' => $this->draw_data['lottery_draw'][0]['id'],
            'lottery_type_data_id' => $type_datum[3]->id,
            'winners' => 2,
            'prizes' => '5.00',
        ]);

        $this->fetch_tickets_result = new Task_Result();
        $this->fetch_tickets_result->set_data([
            'lottery_tickets' => [],
        ]);

        // generate tickets and lines
        // first simple ticket, we need at least two tickets for batching to work properly on full batch (assumption)
        $this->generate_lost_tickets($lottery, $user, $lottery_type);

        // second multi slips ticket
        $second_ticket_data = Test_Factory_Whitelabel_Transaction::create([
            'whitelabel_id' => $user->whitelabel_id,
            'whitelabel_user_id' => $user->id,
            'currency_id' => $user->currency_id,
        ])
            ->with(Test_Factory_Whitelabel_User_Ticket::class, [
                'whitelabel_id' => $user->whitelabel_id,
                'lottery_id' => $lottery->id,
                'lottery_type_id' => $lottery_type->id,
                'valid_to_draw' => $lottery->last_date_local,
                'line_price' => $lottery->price,
                'line_count' => 90,
                'is_synchronized' => true,
            ])
            ->with(Test_Factory_Whitelabel_User_Ticket_Slip::class, [], 6)
            ->get_result();
        $line_counter = 0;
        $get_next_slip = function () use (&$line_counter, &$second_ticket_data): int { // TODO: {Vordis 2019-09-23 13:16:11} there should be an easier way
            if ($line_counter++ === 15) {
                $line_counter = 0;
                next($second_ticket_data['whitelabel_user_ticket_slip']);
            }
            $slip = current($second_ticket_data['whitelabel_user_ticket_slip']);
            return $slip['id'];
        };
        $second_ticket_data['whitelabel_user_ticket_line'] =
            Test_Factory_Whitelabel_User_Ticket_Line::create([
                'whitelabel_user_ticket_slip_id' => $get_next_slip,
                'whitelabel_user_ticket_id' => $second_ticket_data['whitelabel_user_ticket'][0]['id'],
                'lottery_type' => $lottery_type,
                'line_price' => $lottery->price,
            ], 90)->get_result()['whitelabel_user_ticket_line'];

        // generate lcs tickets for slips
        reset($second_ticket_data['whitelabel_user_ticket_slip']);
        $get_next_slip = function () use (&$second_ticket_data): int {
            $slip = current($second_ticket_data['whitelabel_user_ticket_slip']);
            next($second_ticket_data['whitelabel_user_ticket_slip']);
            return $slip['id'];
        };
        $second_ticket_data['lcs_ticket'] =
            Test_Factory_Lcs_Ticket::create([
                'whitelabel_user_ticket_slip_id' => $get_next_slip
            ], 6)->get_result()['lcs_ticket'];

        // generate dummy lcs response
        // we need to prepare six tickets based upon lcs_tickets and lines (15 lines per ticket)
        $get_ticket_data = function (int $iteration) use (&$second_ticket_data): array {
            $lcs_ticket = current($second_ticket_data['lcs_ticket']);
            next($second_ticket_data['lcs_ticket']);
            // mark specified tickets as won
            $additional_data = [];
            if ($iteration === 1 || $iteration === 3) {
                $additional_data = [
                    'status' => 1,
                    'prize' => '5.00',
                ];
            }
            return [
                'uuid' => $lcs_ticket['uuid'],
            ] + $additional_data;
        };
        // TODO: {Vordis 2019-09-23 13:43:23} numbers are not used in check, the only thing important is status and prize
        $get_lines = function (int $iteration) use (&$second_ticket_data): array {
            $start = 15 * $iteration;
            $lines = [];
            for ($i = $start; $i < $start + 15; $i++) {
                $whitelotto_line = $second_ticket_data['whitelabel_user_ticket_line'][$i];
                $lines[] = Test_Mock_Lcs_Ticket_Line::mock([
                    'numbers' => explode(',', $whitelotto_line['numbers']),
                ]);
                // set second and fourth ticket line as won
            }
            if ($iteration === 1 || $iteration === 3) {
                $lines[0]['status'] = 1;
                $lines[0]['lottery_prize'] = [ // TODO: {Vordis 2019-09-23 13:54:56} this should be separate mockable object
                    'per_user' => '5.00',
                    'currency_code' => 'ZMW',
                    'lottery_rule_tier' =>
                    [
                        'slug' => 'match-3',
                    ],
                ];
            }
            return $lines;
        };
        // TODO: {Vordis 2019-09-23 13:38:52} mocking has to be similiar to factories and allow for usage of closures and dynamic number of items
        $lcs_tickets = [];
        for ($i = 0; $i < 6; $i++) {
            $lcs_tickets[] = Test_Mock_Lcs_Ticket::mock($get_ticket_data($i), $get_lines($i));
        }

        // merge results
        $this->update_ticket_attributes($second_ticket_data, $lcs_tickets);

        // create a few more losing tickets
        $this->generate_lost_tickets($lottery, $user, $lottery_type, 10);
    }

    public function test_success_batching(): void
    {
        $this->markTestIncomplete('Error, test case need work');
        // make chain task class unique
        Test_Mock_Loader::load_class_as_mockable(Task_Lotterycentralserver_Chain_Update_Tickets::class, 'Task_Lotterycentralserver_Chain_Update_Tickets_Mockable');
        /**
         * @var Task_Lotterycentralserver_Chain_Update_Tickets $chain_task_mockable
         */
        $chain_task_mockable = new class($this->fetch_tickets_result, 0, 'not-used') extends Task_Lotterycentralserver_Chain_Update_Tickets_Mockable {
            /**
             * How many items should be processed at once.
             */
            const BATCH_SIZE = 6;

            protected $in_transaction = false;
            private $fetch_tickets_result;
            private $slice_index = 0;

            public function __construct(Task_Result $fetch_tickets_result, int $lottery_id, string $slug)
            {
                parent::__construct($lottery_id, $slug);
                $this->fetch_tickets_result = $fetch_tickets_result;
            }

            protected function fetch_tickets_from_lcs(array $batch_array): Task_Result
            {
                // pass as many tickets as there are in batch
                $size = count($batch_array);
                $task_result = new Task_Result();
                $task_result->set_data([
                    'lottery_tickets' => array_slice($this->fetch_tickets_result->get_data()['lottery_tickets'], $this->slice_index, $size)
                ]);
                $this->slice_index += $size;
                return $task_result;
            }

            protected function get_lcs_payout_task_class(): string
            {
                return Dummy_LCS_Payout::class;
            }
        };
        $this->assertTrue(
            $chain_task_mockable::execute($this->fetch_tickets_result, Helpers_Lottery::ZAMBIA_ID, Helpers_Lottery::get_slug(Helpers_Lottery::ZAMBIA_ID))
                ->is_successful()
        );

        $first_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        $this->assertEquals('2', $first_ticket->status);
        $first_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($first_lines[$i]->status, '2'); // line lost
        }
        $second_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][1]['id']);
        $second_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][1]['id']);
        $this->assertEquals('1', $second_ticket->status);
        for ($i = 0; $i < 90; $i++) {
            if ($i === 15 || $i === 45) {
                $this->assertEquals($second_lines[$i]->status, '1'); // line won
            } else {
                $this->assertEquals($second_lines[$i]->status, '2'); // line lost
            }
        }

        for ($j = 2; $j < 12; $j++) { // TODO: {Vordis 2019-09-30 15:25:42} fast and dirty
            $first_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][$j]['id']);
            $this->assertEquals('2', $first_ticket->status);
            $first_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][$j]['id']);
            for ($i = 0; $i < 10; $i++) {
                $this->assertEquals($first_lines[$i]->status, '2'); // line lost
            }
        }
    }

    public function test_success_single_batch(): void
    {
        $this->markTestIncomplete('Error, test case need work');
        // make chain task class unique
        Test_Mock_Loader::load_class_as_mockable(Task_Lotterycentralserver_Chain_Update_Tickets::class, 'Task_Lotterycentralserver_Chain_Update_Tickets_Mockable');
        /**
         * @var Task_Lotterycentralserver_Chain_Update_Tickets $chain_task_mockable
         */
        $chain_task_mockable = new class($this->fetch_tickets_result, 0, 'not-used') extends Task_Lotterycentralserver_Chain_Update_Tickets_Mockable {
            /**
             * How many items should be processed at once.
             */
            const BATCH_SIZE = 10000;

            protected $in_transaction = false;
            private $fetch_tickets_result;
            private $slice_index = 0;

            public function __construct(Task_Result $fetch_tickets_result, int $lottery_id, string $slug)
            {
                parent::__construct($lottery_id, $slug);
                $this->fetch_tickets_result = $fetch_tickets_result;
            }

            protected function fetch_tickets_from_lcs(array $batch_array): Task_Result
            {
                // pass as many tickets as there are in batch
                $size = count($batch_array);
                $task_result = new Task_Result();
                $task_result->set_data([
                    'lottery_tickets' => array_slice($this->fetch_tickets_result->get_data()['lottery_tickets'], $this->slice_index, $size)
                ]);
                $this->slice_index += $size;
                return $task_result;
            }

            protected function get_lcs_payout_task_class(): string
            {
                return Dummy_LCS_Payout::class;
            }
        };
        $this->assertTrue(
            $chain_task_mockable::execute($this->fetch_tickets_result, Helpers_Lottery::ZAMBIA_ID, Helpers_Lottery::get_slug(Helpers_Lottery::ZAMBIA_ID))
                ->is_successful()
        );

        $first_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        $this->assertEquals('2', $first_ticket->status);
        $first_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($first_lines[$i]->status, '2'); // line lost
        }
        $second_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][1]['id']);
        $second_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][1]['id']);
        $this->assertEquals('1', $second_ticket->status);
        for ($i = 0; $i < 90; $i++) {
            if ($i === 15 || $i === 45) {
                $this->assertEquals($second_lines[$i]->status, '1'); // line won
            } else {
                $this->assertEquals($second_lines[$i]->status, '2'); // line lost
            }
        }

        for ($j = 2; $j < 12; $j++) { // TODO: {Vordis 2019-09-30 15:25:42} fast and dirty
            $first_ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][$j]['id']);
            $this->assertEquals('2', $first_ticket->status);
            $first_lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][$j]['id']);
            for ($i = 0; $i < 10; $i++) {
                $this->assertEquals($first_lines[$i]->status, '2'); // line lost
            }
        }
    }
}
