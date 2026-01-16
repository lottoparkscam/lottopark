<?php

class Tests_Feature_Classes_Factory_Orm_Tier extends Test_Feature
{
    public function test_it_throws_exception_when_no_context(): void
    {
        $this->markTestIncomplete('Need work');
        $this->expectException(Exception::class);
        Factory_Orm_Tier::forge()->build();
    }

    public function test_it_creates_raffle_based_data(): void
    {
        $random_raffle = Factory_Orm_Raffle::forge()->randomize()->build();
        $tier = Factory_Orm_Tier::forge()->for_raffle($random_raffle)->build();
        $this->assertRelations($tier);
        $this->assertSame($tier->getFirstRule()->id, $random_raffle->raffle_rule_id);
    }

    /**
     * @param RaffleRuleTier $rule
     */
    private function assertRelations($rule): void
    {
        $this->assertInstanceOf(RaffleRuleTier::class, $rule);

        $this->assertSame($rule->currency_id, (int)$rule->currency->id);
        $this->assertSame($rule->raffle_rule_id, (int)$rule->rule()->id);

        $this->assertNotEmpty($rule->rule());
        $this->assertNotEmpty($rule->currency);
    }
}
