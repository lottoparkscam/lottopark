<?php

use Fuel\Core\DB;
use Models\Raffle;
use Models\Currency;
use Models\RafflePrize;
use Orm\RecordNotFound;
use Models\RaffleRuleTier;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

/**
 * Creates and populates draw by lcs api response.
 */
class Services_Raffle_Prize_Factory
{
    private Raffle $raffle_dao;
    private RaffleRuleTier $tier_dao;
    private Validator_Lcs_Prizes $lcs_prizes_validator;
    private Currency $currency_dao;
    private RafflePrize $prize_dao;

    public function __construct(
        Raffle $raffle,
        RaffleRuleTier $tier,
        Currency $currency,
        Validator_Lcs_Prizes $lcs_prizes_validator,
        RafflePrize $prize
    ) {
        $this->raffle_dao = $raffle;
        $this->tier_dao = $tier;
        $this->currency_dao = $currency;
        $this->lcs_prizes_validator = $lcs_prizes_validator;
        $this->prize_dao = $prize;
    }

    /**
     * Creates the whole draw data, including prizes and tiers.
     *
     * @param array $lcs_lottery_prizes - from /draws endpoint <lottery_prizes> key values
     * @param string $raffle_slug
     *
     * @param int $draw_id
     *
     * @return array|RafflePrize[]
     * @throws Throwable
     */
    public function create_from_lcs_data(array $lcs_lottery_prizes, string $raffle_slug, int $draw_id): array
    {
        $raffle = $this->get_and_verify_raffle($raffle_slug);
        DB::start_transaction();
        try {
            $prizes = $this->verify_and_create_prizes_from_lcs_data($lcs_lottery_prizes, $raffle, $draw_id);
        } catch (Throwable $exception) {
            Db::rollback_transaction();
            throw $exception;
        }
        DB::commit_transaction();

        return $prizes;
    }

    private function get_and_verify_raffle(string $slug): Raffle
    {
        return $this->raffle_dao->get_by_slug_with_currency_and_rule($slug);
    }

    /**
     * @param array $lcs_lottery_prizes
     * @param Raffle $raffle
     * @param int $draw_id
     *
     * @return array|RafflePrize[]
     * @throws Exception
     * 
     */
    private function verify_and_create_prizes_from_lcs_data(array $lcs_lottery_prizes, Raffle $raffle, int $draw_id): array
    {
        $prizes = [];

        foreach ($lcs_lottery_prizes as $prize_data) {
            $this->lcs_prizes_validator->validate($prize_data);

            /** @var object $prize */
            $prize = $this->prize_dao::first_or_create($prize_data, [
                ['per_user', '=', $prize_data['per_user']],
                ['raffle_draw_id', '=', $draw_id],
                ['raffle_rule_id', '=', $raffle->raffle_rule_id],
            ]);
            $prize->raffle_draw_id = $draw_id;
            $prize->raffle_rule_id = $raffle->raffle_rule_id;
            $prize->currency = $this->get_currency_by_code($prize_data['currency_code']);
            $prize->tier = $this->get_tier_by_slug($prize_data['lottery_rule_tier']['slug'], $raffle->raffle_rule_id);

            $this->prize_dao->store($prize);

            $prizes[] = $prize;
        }

        return $prizes;
    }

    private function get_currency_by_code(string $currency_code): Currency
    {
        return $this->currency_dao->get_by_code($currency_code);
    }

    /**
     * @param string $tier_slug
     * @param int $rule_id
     *
     * @return RaffleRuleTier
     * @throws RecordNotFound
     * @throws Exception
     */
    private function get_tier_by_slug(string $tier_slug, int $rule_id): RaffleRuleTier
    {
        try {
            return $this->tier_dao->push_criteria(new Model_Orm_Criteria_Where('raffle_rule_id', $rule_id))->get_by_slug($tier_slug);
        } catch (RecordNotFound $exception) {
            throw new RecordNotFound(sprintf('Unable to find Tier for raffle_rule = %d with slug = %s', $rule_id, $tier_slug));
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
