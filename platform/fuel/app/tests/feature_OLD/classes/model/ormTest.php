<?php

use Orm\RecordNotFound;

/**
 * Helper test for testing ORM.
 */
class Tests_Feature_Model_Orm extends Test_Feature
{
    /** @var RaffleRule */
    private $rule_dao;

    public function setUp(): void
    {
        parent::setUp();
        $this->rule_dao = Container::get(RaffleRule::class);
        $currency_dao = Container::get(Currency::class);
        $ruleHasResults = $this->rule_dao->has_results();
        $this->assertFalse($ruleHasResults);
        $currencyHasResults = $currency_dao->has_results();
        $this->assertFalse($currencyHasResults);
    }

    public function test_it_limits_results_only_to_relation(): void
    {
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_With_Relation('tiers.currency'));
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_Where('tiers.currency.code', 'aa2'));

        $this->expectException(RecordNotFound::class);
        $this->rule_dao->get_one();
    }

    public function test_it_return_result_from_relation(): void
    {
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_With_Relation('tiers.currency'));
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_Where('tiers.currency.code', 'USD'));

        $result = $this->rule_dao->get_one();
        $this->assertNotEmpty($result);
    }

    public function test_it_returns_one_result_when_get_invoked(): void
    {
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_With_Relation('tiers.currency'));
        $this->rule_dao->push_criteria(new Model_Orm_Criteria_Where('tiers.currency.code', 'USD'));

        $this->assertNotEmpty($this->rule_dao->get_one());
    }
}
