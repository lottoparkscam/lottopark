<?php

use Carbon\Carbon;

class Tests_Feature_Lotto_Lotteries_SaturdayLottoAUTest extends Test_Feature
{
    /** @var array */
    protected $lottery;

    /** @var Lotto_Lotteries_Ltech */
    protected $ltech;


    public function setUp(): void
    {
        parent::setUp();

        $lotteries = Model_Lottery::get_all_lotteries();
        $lotteries = $lotteries['__by_slug'];
        $this->lottery = $lotteries['saturday-lotto-au'];

        $this->ltech = new Lotto_Lotteries_SaturdayLottoAU();

        $date = new ReflectionProperty('Lotto_Lotteries_Ltech', 'date');
        $date->setAccessible(true);
        $draw_date = new DateTime('2020-11-14' . " " . '19:30', new DateTimeZone('Australia/Sydney'));
        $date->setValue($this->ltech, $draw_date);
    }

    /** @test */
    public function test_get_lottery_prizes(): void
    {
        $get_lottery_prizes = new ReflectionMethod('Lotto_Lotteries_Ltech', 'get_lottery_prizes');
        $get_lottery_prizes->setAccessible(true);
        
        $lottery_prizes = $get_lottery_prizes->invokeArgs($this->ltech, array($this->lottery));

        $assumed_slugs = [
            'match-6', 'match-5-s', 'match-5', 'match-4', 'match-3-s', 'match-3'
        ];

        $_data = new ReflectionProperty('Model_Lottery_Type_Data', '_data');
        $_data->setAccessible(true);

        $i = 0;
        foreach ($lottery_prizes as $lottery_prize) {
            $lottery_prize_data = $_data->getValue($lottery_prize);
            $this->assertEquals($assumed_slugs[$i], $lottery_prize_data['slug']);
            $i++;
        }
    }

    /** @test */
    public function test_sort_final_draw(): void
    {
        $sort_final_draw = new ReflectionMethod('Lotto_Lotteries_Ltech', 'sort_final_draw');
        $sort_final_draw->setAccessible(true);
        
        // Ltech Integration Guide 2020-10-16 - Saturday Lotto (AU)
        // match-1-s changed to match-3
        $final_draw = new stdClass();
        $final_draw->prizes = [
            "match-6" => "1347826.09",
            "match-5-s" => "10160.40",
            "match-5" => "1075.50",
            "match-4" => "32.30",
            "match-3-s" => "22.60",
            "match-3" => "14.50"
        ];
        $final_draw->winners = [
            "match-6" => 23,
            "match-5-s" => 254,
            "match-5" => 5178,
            "match-4" => 260815,
            "match-3-s" => 625384,
            "match-3" => 1256464
        ];

        $sorted_prizes = $sort_final_draw->invokeArgs($this->ltech, array($this->lottery, $final_draw));

        // TODO: assertions
        $assumed_prizes = [
            "1347826.09",   // match-6
            "10160.40",     // match-5-s
            "1075.50",      // match-5
            "32.30",        // match-4
            "22.60",        // match-3-s
            "14.50"         // match-3
        ];
        $assumed_winners = [
            23,         // match-6
            254,        // match-5-s
            5178,       // match-5
            260815,     // match-4
            625384,     // match-3-s
            1256464     // match-3
        ];

        $this->assertEquals($assumed_prizes, $sorted_prizes['prizes']);
        $this->assertEquals($assumed_winners, $sorted_prizes['winners']);
    }

    /** @test */
    public function test_validate_draw_date(): void
    {
        $validate_draw_date = new ReflectionMethod('Lotto_Lotteries_Ltech', 'validate_draw_date');
        $validate_draw_date->setAccessible(true);

        $date = Carbon::parse('2020-11-14' . " " . '19:30', new DateTimeZone('Australia/Sydney'));

        $is_valid = $validate_draw_date->invokeArgs($this->ltech, array($this->lottery, $date));

        $this->assertTrue($is_valid);
    }

    /** @test */
    public function test_check_prize_slugs(): void
    {
        $this->markTestSkipped('Need to be fixed');
        $check_prize_slugs = new ReflectionMethod('Lotto_Lotteries_Ltech', 'check_prize_slugs');
        $check_prize_slugs->setAccessible(true);

        // Valid prizes
        $final_draw = new stdClass();
        $final_draw->prizes = [
            "match-6" => "1347826.09",
            "match-5-s" => "10160.40",
            "match-5" => "1075.50",
            "match-4" => "32.30",
            "match-3-s" => "22.60",
            "match-3" => "14.50"
        ];
        $final_draw->winners = [
            "match-6" => 23,
            "match-5-s" => 254,
            "match-5" => 5178,
            "match-4" => 260815,
            "match-3-s" => 625384,
            "match-3" => 1256464
        ];

        $prizes_valid = $check_prize_slugs->invokeArgs($this->ltech, array($this->lottery, $final_draw));

        $this->assertTrue($prizes_valid);

        // Invalid prizes slugs
        $final_draw = new stdClass();
        $final_draw->prizes = [
            "match-6" => "1347826.09",
            "match-3" => "14.50"
        ];
        $final_draw->winners = [
            "match-6" => 23,
            "match-5-s" => 254,
            "match-5" => 5178,
            "match-4" => 260815,
            "match-3-s" => 625384,
            "match-3" => 1256464
        ];

        $prizes_valid = $check_prize_slugs->invokeArgs($this->ltech, array($this->lottery, $final_draw));

        $this->assertFalse($prizes_valid);

        // Invalid winners slugs
        $final_draw = new stdClass();
        $final_draw->prizes = [
            "match-6" => "1347826.09",
            "match-5-s" => "10160.40",
            "match-5" => "1075.50",
            "match-4" => "32.30",
            "match-3-s" => "22.60",
            "match-3" => "14.50"
        ];
        $final_draw->winners = [
            "match-6" => 23,
            "match-5-s" => 254,
            "match-5" => 5178
        ];

        $prizes_valid = $check_prize_slugs->invokeArgs($this->ltech, array($this->lottery, $final_draw));

        $this->assertFalse($prizes_valid);
    }
}
