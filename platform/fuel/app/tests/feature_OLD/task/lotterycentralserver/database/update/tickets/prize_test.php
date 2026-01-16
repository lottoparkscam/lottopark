<?php

final class Prize_Test extends Test_Feature
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
    private $tickets_data;

    /**
     * Data created by factories for test.
     * @var array
     */
    private $draw_data;

    /**
     * fetch_tickets_result.
     * @var Task_Result
     */
    private $fetch_tickets_result;

    /**
     * lottery.
     * @var Model_Lottery
     */
    private $lottery;

    public function setUp(): void
    {
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

        // having those models now create dummy tickets for next draw
        $user_numbers = [
            '6,11,13,14,26,29',
            '8,14,17,19,20,35',
            '6,10,11,17,23,32',
            '5,10,14,22,27,31',
            '2,5,6,9,11,34',
            '4,12,13,16,25,27',
            '2,17,18,30,34,35',
            '4,13,14,17,28,32',
            '5,8,14,19,28,32',
            '10,13,14,20,26,34',
            '4,14,15,24,26,28',
            '12,17,19,24,33,34',
            '6,9,10,23,31,32',
            '9,18,25,26,31,34',
            '10,12,26,27,31,33',
            '7,11,13,21,25,34',
            '12,23,24,29,31,33',
            '1,14,16,20,24,29', // winning line
            '2,18,19,25,35,36',
            '8,15,18,21,24,26',
        ];
        $get_next_number = function () use (&$user_numbers): string {
            $numbers = current($user_numbers);
            next($user_numbers);
            return $numbers;
        };
        $this->tickets_data = Test_Factory_Whitelabel_Transaction::create([
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
                'line_count' => 20,
                'is_synchronized' => true,
            ])
            ->with_multiple(
                [
                    Test_Factory_Whitelabel_User_Ticket_Slip::class => [],
                    Test_Factory_Whitelabel_User_Ticket_Line::class => [
                        'values' => [
                            'lottery_type' => $lottery_type,
                            'numbers' => $get_next_number,
                        ],
                        'count' => 15
                    ],
                ],
                true
            )
            ->get_result();
        $second_slip = Test_Factory_Whitelabel_User_Ticket_Slip::create([
            'whitelabel_user_ticket_id' => $this->tickets_data['whitelabel_user_ticket'][0]['id']
        ])->with(Test_Factory_Whitelabel_User_Ticket_Line::class, [
            'whitelabel_user_ticket_id' => $this->tickets_data['whitelabel_user_ticket'][0]['id'],
            'line_price' => $lottery->price,
            'lottery_type' => $lottery_type,
            'numbers' => function () use (&$user_numbers): string {
                $numbers = current($user_numbers);
                next($user_numbers);
                return $numbers;
            },
        ], 5)
            ->get_result();
        // merge second slip
        $this->tickets_data['whitelabel_user_ticket_slip'] = array_merge($this->tickets_data['whitelabel_user_ticket_slip'], $second_slip['whitelabel_user_ticket_slip']);
        $this->tickets_data['whitelabel_user_ticket_line'] = array_merge($this->tickets_data['whitelabel_user_ticket_line'], $second_slip['whitelabel_user_ticket_line']);
        // generate lcs tickets for slips
        $this->tickets_data['lcs_ticket'] = [
            Test_Factory_Lcs_Ticket::create([
                'whitelabel_user_ticket_slip_id' => $this->tickets_data['whitelabel_user_ticket_slip'][0]['id']
            ])->get_result()['lcs_ticket'][0],
            Test_Factory_Lcs_Ticket::create([
                'whitelabel_user_ticket_slip_id' => $this->tickets_data['whitelabel_user_ticket_slip'][1]['id']
            ])->get_result()['lcs_ticket'][0],
        ];

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
            'winners' => 1,
            'prizes' => 5,
        ]);

        // generate dummy lcs response
        reset($user_numbers);
        $lines = [];
        for ($i = 0; $i < 15; $i++) {
            $lines[] = Test_Mock_Lcs_Ticket_Line::mock([
                'numbers' => $get_next_number()
            ]);
        }
        $lcs_first_ticket = Test_Mock_Lcs_Ticket::mock([
            'uuid' => $this->tickets_data['lcs_ticket'][0]['uuid']
        ], $lines);
        $lines = [];
        for ($i = 0; $i < 5; $i++) {
            $numbers = $get_next_number();
            $base = [];
            if ($numbers === '1,14,16,20,24,29') {
                $base = [
                    'status' => 1,
                    'lottery_prize' => [
                        'per_user' => '5.00',
                        'currency_code' => 'ZMW',
                        'lottery_rule_tier' =>
                        [
                            'slug' => 'match-3',
                        ],
                    ]
                ];
            }
            $lines[] = Test_Mock_Lcs_Ticket_Line::mock($base + [
                'numbers' => $numbers
            ]);
        }
        $lcs_second_ticket = Test_Mock_Lcs_Ticket::mock([
            'uuid' => $this->tickets_data['lcs_ticket'][1]['uuid'],
            'status' => 1,
            'prize' => 5,
        ], $lines);
        $this->fetch_tickets_result = new Task_Result();
        $this->fetch_tickets_result->set_data([
            'lottery_tickets' => [$lcs_first_ticket, $lcs_second_ticket]
        ]);
    }

    public function test_success(): void
    {
        $this->markTestIncomplete('Error, test case need work');
        // load lottery - needed for prize calculation
        $lottery = &$this->lottery;
        $lottery_currency_id = $lottery->currency_id;

        // first check if last draw has pending tickets
        $last_draw = Model_Lottery_Draw::last_for_lottery_by_draw_no($lottery->id);

        if ($last_draw === null) {
            throw new Exception('no draw');
        }

        if (!$last_draw->has_pending_tickets) {
            throw new Exception('no pending tickets');
        }

        // fetch prizes
        $prizes = $last_draw->prizes_with_type_data(['winners', 'prizes', 'is_jackpot', 'lottery_type_data_id']);
        if ($prizes === null) {
            throw new Exception("Failed to load prizes for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }

        // load currencies - for calculation of prizes (prize_usd, prize_user, net_, uncovered)
        $currencies = Helpers_Currency::getCurrencies();
        if ($currencies === null) {
            throw new Exception("Failed to load currencies for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }

        // load provider for lottery
        $lottery_provider = Model_Lottery_Provider::last_for_lottery($lottery->id);
        if ($lottery_provider === null) {
            throw new Exception("Failed to load provider for lottery {$this->lottery_slug}, draw {$last_draw->id}");
        }
        $batch_array = Model_Whitelabel_User_Ticket::pending_for_lottery_with_lcs_ticket_user_and_whitelabel_task($lottery->id, $lottery->last_date_local)
            ->execute()
            ->as_array();

        $this->assertTrue(Task_Lotterycentralserver_Database_Update_Tickets_Prize::execute(
            $this->fetch_tickets_result,
            $batch_array,
            $prizes,
            $currencies,
            $lottery_provider,
            $lottery_currency_id
        )->is_successful());

        // look into database - check if tickets have proper statuses
        $ticket = Model_Whitelabel_User_Ticket::find_one_by('id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        $lines = Model_Whitelabel_User_Ticket_Line::find_by('whitelabel_user_ticket_id', $this->tickets_data['whitelabel_user_ticket'][0]['id']);
        $this->assertEquals('1', $ticket->status);
        for ($i = 0; $i < 20; $i++) {
            if ($i === 17) {
                $this->assertEquals($lines[$i]->status, '1'); // line won
            } else {
                $this->assertEquals($lines[$i]->status, '2'); // line lost
            }
        }
    }
}
