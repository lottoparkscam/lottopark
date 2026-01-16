<?php


use Models\Lottery;
use Models\WhitelabelLottery;
use Fuel\Migrations\Whitelabel_Lottery;

class Test_Feature_Classes_lotto_Helper extends Test_Feature
{
    /** @var Model_Whitelabel */
    private $whitelabel;

    /** @var Lottery  */
    private $powerball_lottery;

    /** @var Whitelabel_Lottery */
    private $powerball_whitelabel_lottery;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = Model_Whitelabel::find_by_pk(1);
        Lotto_Settings::getInstance()->set('whitelabel', $this->whitelabel);

        $this->powerball_lottery = Lottery::find('first', [
            'where' => [
                'slug' => 'powerball'
            ]
        ]);

        $this->powerball_lottery->next_date_local = (new DateTime())->format('Y-m-d');
        $this->powerball_lottery->save();

        /** @var WhitelabelLottery $powerball_whitelabel_lottery */
        $this->powerball_whitelabel_lottery = WhitelabelLottery::find('first', [
            'where' => [
                'whitelabel_id' => 1,
                'lottery_id' => $this->powerball_lottery['id']
            ]
        ]);
    }

    public function test_is_lottery_closed_on_correct_datetime()
    {
        $powerball_lottery = $this->powerball_lottery;
        $is_closed = function () use ($powerball_lottery) {
            return Lotto_Helper::is_lottery_closed($powerball_lottery->to_array(), null, $this->whitelabel->to_array());
        };
        $powerball_lottery_provider = $this->powerball_whitelabel_lottery->lottery_provider;

        // correct closing time = now - offset - 1h
        $now = new DateTimeImmutable("now", new DateTimeZone($this->powerball_lottery->timezone));

        // check one hour before correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->modify('+2 hour')
            ->modify(-($powerball_lottery_provider->offset) . ' hours')
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertFalse($is_closed());

        // check 1 minute before correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->modify('+1 hour')
            ->modify('+1 minute')
            ->modify($powerball_lottery_provider->offset . 'hours')
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertFalse($is_closed());

        // exact correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->modify('+1 hour')
            ->modify($powerball_lottery_provider->offset . 'hours')
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertTrue($is_closed());

        // check 1 hour after correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->modify($powerball_lottery_provider->offset . 'hours')
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertTrue($is_closed());

        // check 1 hour + offset hours after correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertTrue($is_closed());

        // check 2 hour + offset hours after correct closing time
        $powerball_lottery_provider->closing_time = $now
            ->modify('-1 hour')
            ->format('H:i:s');
        $powerball_lottery_provider->save();

        $this->assertTrue($is_closed());
    }
}
