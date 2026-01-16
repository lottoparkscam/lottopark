<?php

final class Tests_Feature_Model_Raffle_Orm_Raffle extends Test_Feature
{
    /** @var Raffle */
    private $raffle_dao;
    /** @var Raffle */
    private $raffle;

    public function setUp(): void
    {
        parent::setUp();

        $this->raffle_dao = Container::get(Raffle::class);
        $this->raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule('faireum-raffle');

        if (!$this->raffle->is_sell_temporary_disabled) {
            $this->raffle->is_sell_enabled = false;
            $this->raffle->is_sell_limitation_enabled = true;
            $this->raffle->save();
        }
    }

    public function test_it_returns_temporary_disabled_raffles(): void
    {
        $results = $this->raffle_dao->get_temporary_disabled();
        $this->assertNotEmpty($results);
        foreach ($results as $raffle) {
            $this->assertTrue($raffle->is_sell_limitation_enabled);
        }
    }
}
