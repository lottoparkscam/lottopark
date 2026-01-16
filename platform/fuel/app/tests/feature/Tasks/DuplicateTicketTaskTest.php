<?php

namespace Tests\Feature\Tasks;

use Models\WhitelabelUserTicketLine;
use Models\WhitelabelUserTicketSlip;
use Task\LotteryCentralServer\Send\DuplicateTicketTask;
use Fuel\Core\DB;
use Models\LcsTicket;
use Models\WhitelabelUserTicket;
use Test_Feature;

/** @covers DuplicateTicketTask */
class DuplicateTicketTaskTest extends Test_Feature
{
    /** @test */
    public function execute_TicketPurchasedInLcs_TaskFixesOneUnsynchronizedTicketCorrectly()
    {
        // Workaround missing relations in database
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelUserTicket = new WhitelabelUserTicket();
        // Essential data for the test
        $whitelabelUserTicket->id = 999999;
        $whitelabelUserTicket->is_synchronized = false;
        $whitelabelUserTicket->paid = true;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->token = 436082015;
        self::addRequiredDataToTicket($whitelabelUserTicket);
        $whitelabelUserTicket->save();
        $this->addSlipAndLine($whitelabelUserTicket, 1234567);

        $where = [
            ['token', '=', 436082015],
            ['is_synchronized', '=', 0],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 1);

        $jsonLcsResponse = '{
            "error": {
            "message": "There are already used tokens",
                "usedTokens": {
                    "436082015-1234567": "967edc80-1b9a-4778-9bfd-93acb4553c7a"
                }
            }
         }';

        $jsonWhitelottoRequestPayload = '{
                "tickets": {
                    "1234567": {
                        "token": "436082015-1234567",
                        "amount": 10.8,
                        "ip": "172.21.0.1",
                        "lines": [
                            {
                                "numbers": [
                                    [
                                        "1",
                                        "4",
                                        "31",
                                        "41",
                                        "46"
                                    ],
                                    [
                                        "6",
                                        "10"
                                    ]
                                ]
                            }
                        ]
                    }
                }
            }';

        $lcsResponse = json_decode($jsonLcsResponse, true);
        $requestPayload = json_decode($jsonWhitelottoRequestPayload, true);

        DuplicateTicketTask::execute($lcsResponse, $requestPayload);

        $where = [
            ['token', '=', 436082015],
            ['is_synchronized', '=', 1],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 1);

        $where = [
            ['whitelabel_user_ticket_slip_id', '=', 1234567],
        ];
        $this->assertDbHasRows(LcsTicket::class, $where, 1);
    }

    /** @test */
    public function execute_TicketPurchasedInLcs_TaskFixesTwoUnsynchronizedTicketsCorrectly()
    {
        // Workaround missing relations in database
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelUserTicket = new WhitelabelUserTicket();
        // Essential data for the test
        $whitelabelUserTicket->id = 999999;
        $whitelabelUserTicket->is_synchronized = false;
        $whitelabelUserTicket->paid = true;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->token = 436082015;
        self::addRequiredDataToTicket($whitelabelUserTicket);
        $whitelabelUserTicket->save();
        $this->addSlipAndLine($whitelabelUserTicket, 1234567);

        $whitelabelUserTicket = new WhitelabelUserTicket();
        // Essential data for the test
        $whitelabelUserTicket->id = 999991;
        $whitelabelUserTicket->is_synchronized = false;
        $whitelabelUserTicket->paid = true;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->token = 636082015;
        self::addRequiredDataToTicket($whitelabelUserTicket);
        $whitelabelUserTicket->save();
        $this->addSlipAndLine($whitelabelUserTicket, 7654321);

        $where = [
            ['token', 'IN', [436082015,636082015]],
            ['is_synchronized', '=', 0],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 2);

        $jsonLcsResponse = '{
            "error": {
            "message": "There are already used tokens",
                "usedTokens": {
                    "436082015-1234567": "967edc80-1b9a-4778-9bfd-93acb4553c7a",
                    "636082015-7654321": "868edc80-1b9a-4778-9bfd-93acb4553c1c"
                }
            }
         }';

        $jsonWhitelottoRequestPayload = '{
                "tickets": {
                    "1234567": {
                        "token": "436082015-1234567",
                        "amount": 10.8,
                        "ip": "172.21.0.1",
                        "lines": [
                            {
                                "numbers": [
                                    [
                                        "1",
                                        "4",
                                        "31",
                                        "41",
                                        "46"
                                    ],
                                    [
                                        "6",
                                        "10"
                                    ]
                                ]
                            }
                        ]
                    },
                    "7654321": {
                        "token": "636082015-7654321",
                        "amount": 10.8,
                        "ip": "172.21.0.1",
                        "lines": [
                            {
                                "numbers": [
                                    [
                                        "1",
                                        "4",
                                        "31",
                                        "41",
                                        "46"
                                    ],
                                    [
                                        "6",
                                        "10"
                                    ]
                                ]
                            }
                        ]
                    }
                }
            }';

        $lcsResponse = json_decode($jsonLcsResponse, true);
        $requestPayload = json_decode($jsonWhitelottoRequestPayload, true);

        DuplicateTicketTask::execute($lcsResponse, $requestPayload);

        $where = [
            ['token', 'IN', [436082015, 636082015]],
            ['is_synchronized', '=', 1],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 2);

        $where = [
            ['whitelabel_user_ticket_slip_id', 'IN', [1234567, 7654321]],
        ];
        $this->assertDbHasRows(LcsTicket::class, $where, 2);
    }

    /** @test */
    public function execute_OneAlreadyPurchasedAndOneNotPurchased_AlreadyPurchasedTicketFixedAsSynchronized()
    {
        // Workaround missing relations in database
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelUserTicket = new WhitelabelUserTicket();
        // Essential data for the test
        $whitelabelUserTicket->id = 999999;
        $whitelabelUserTicket->is_synchronized = false;
        $whitelabelUserTicket->paid = true;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->token = 436082015;
        self::addRequiredDataToTicket($whitelabelUserTicket);
        $whitelabelUserTicket->save();
        $this->addSlipAndLine($whitelabelUserTicket, 1234567);

        $whitelabelUserTicket = new WhitelabelUserTicket();
        // Essential data for the test
        $whitelabelUserTicket->id = 999991;
        $whitelabelUserTicket->is_synchronized = false;
        $whitelabelUserTicket->paid = true;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->token = 636082015;
        self::addRequiredDataToTicket($whitelabelUserTicket);
        $whitelabelUserTicket->save();
        $this->addSlipAndLine($whitelabelUserTicket, 7654321);

        $where = [
            ['token', 'IN', [436082015,636082015]],
            ['is_synchronized', '=', 0],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 2);

        $jsonLcsResponse = '{
            "error": {
            "message": "There are already used tokens",
                "usedTokens": {
                    "436082015-1234567": "967edc80-1b9a-4778-9bfd-93acb4553c7a"
                }
            }
         }';

        $jsonWhitelottoRequestPayload = '{
                "tickets": {
                    "1234567": {
                        "token": "436082015-1234567",
                        "amount": 10.8,
                        "ip": "172.21.0.1",
                        "lines": [
                            {
                                "numbers": [
                                    [
                                        "1",
                                        "4",
                                        "31",
                                        "41",
                                        "46"
                                    ],
                                    [
                                        "6",
                                        "10"
                                    ]
                                ]
                            }
                        ]
                    },
                    "7654321": {
                        "token": "636082015-7654321",
                        "amount": 10.8,
                        "ip": "172.21.0.1",
                        "lines": [
                            {
                                "numbers": [
                                    [
                                        "1",
                                        "4",
                                        "31",
                                        "41",
                                        "46"
                                    ],
                                    [
                                        "6",
                                        "10"
                                    ]
                                ]
                            }
                        ]
                    }
                }
            }';

        $lcsResponse = json_decode($jsonLcsResponse, true);
        $requestPayload = json_decode($jsonWhitelottoRequestPayload, true);

        DuplicateTicketTask::execute($lcsResponse, $requestPayload);

        $where = [
            ['token', 'IN', [436082015, 636082015]],
            ['is_synchronized', '=', 1],
        ];
        $this->assertDbHasRows(WhitelabelUserTicket::class, $where, 1);

        $where = [
            ['whitelabel_user_ticket_slip_id', 'IN', [1234567, 7654321]],
        ];
        $this->assertDbHasRows(LcsTicket::class, $where, 1);
    }

    private static function addRequiredDataToTicket(WhitelabelUserTicket $whitelabelUserTicket): WhitelabelUserTicket
    {
        // Required data, but not test related below
        $whitelabelUserTicket->whitelabel_id = 1;
        $whitelabelUserTicket->whitelabel_user_id = 1;
        $whitelabelUserTicket->lottery_id = 32;
        $whitelabelUserTicket->lottery_type_id = 32;
        $whitelabelUserTicket->currency_id = 2;
        $whitelabelUserTicket->valid_to_draw = '2022-06-07 20:45:00';
        $whitelabelUserTicket->draw_date = '2022-06-07 20:45:00';
        $whitelabelUserTicket->amount_local = 4;
        $whitelabelUserTicket->amount = 4;
        $whitelabelUserTicket->amount_usd = 4;
        $whitelabelUserTicket->date = '2022-06-06 20:11:00';
        $whitelabelUserTicket->payout = false;
        $whitelabelUserTicket->cost_local = 2;
        $whitelabelUserTicket->cost = 2;
        $whitelabelUserTicket->cost_usd = 2;
        $whitelabelUserTicket->income_local = 1;
        $whitelabelUserTicket->income_value = 1;
        $whitelabelUserTicket->income = 1;
        $whitelabelUserTicket->income_usd = 1;
        $whitelabelUserTicket->income_type = 1;
        $whitelabelUserTicket->is_insured = true;
        $whitelabelUserTicket->tier = 1;
        $whitelabelUserTicket->margin_local = 0;
        $whitelabelUserTicket->margin = 0;
        $whitelabelUserTicket->margin_usd = 0;
        $whitelabelUserTicket->margin_value = 0;
        $whitelabelUserTicket->bonus_amount_local = 0;
        $whitelabelUserTicket->bonus_amount_payment = 0;
        $whitelabelUserTicket->bonus_amount_usd = 0;
        $whitelabelUserTicket->bonus_amount = 0;
        $whitelabelUserTicket->bonus_amount_manager = 0;
        $whitelabelUserTicket->bonus_cost_local = 0;
        $whitelabelUserTicket->bonus_cost = 0;
        $whitelabelUserTicket->bonus_cost_usd = 0;
        $whitelabelUserTicket->bonus_cost_manager = 0;
        $whitelabelUserTicket->has_ticket_scan = false;
        $whitelabelUserTicket->ip = '172.21.0.1';
        $whitelabelUserTicket->line_count = 1;

        return $whitelabelUserTicket;
    }

    private function addSlipAndLine(WhitelabelUserTicket $whitelabelUserTicket, int $slipId): void
    {
        $whitelabelUserTicketSlip = new WhitelabelUserTicketSlip();
        $whitelabelUserTicketSlip->id = $slipId;
        $whitelabelUserTicketSlip->whitelabel_user_ticket_id = $whitelabelUserTicket->id;
        $whitelabelUserTicketSlip->whitelabel_lottery_id = 1;
        $whitelabelUserTicketSlip->save();

        $whitelabelUserTicketLine = new WhitelabelUserTicketLine();
        $whitelabelUserTicketLine->whitelabel_user_ticket_id = $whitelabelUserTicket->id;
        $whitelabelUserTicketLine->whitelabel_user_ticket_slip_id = $whitelabelUserTicketSlip->id;
        $whitelabelUserTicketLine->numbers = "[1,2,3]";
        $whitelabelUserTicketLine->bnumbers = "";
        $whitelabelUserTicketLine->amount_local = 10;
        $whitelabelUserTicketLine->amount = 10;
        $whitelabelUserTicketLine->amount_usd = 10;
        $whitelabelUserTicketLine->status = 0;
        $whitelabelUserTicketLine->payout = 100;
        $whitelabelUserTicketLine->bonus_amount_local = 10;
        $whitelabelUserTicketLine->bonus_amount_payment = 10;
        $whitelabelUserTicketLine->bonus_amount_usd = 10;
        $whitelabelUserTicketLine->bonus_amount = 10;
        $whitelabelUserTicketLine->bonus_amount_manager = 10;
        $whitelabelUserTicketLine->save();
    }
}
