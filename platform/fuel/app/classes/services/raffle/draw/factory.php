<?php

use Models\Raffle;
use Models\Currency;
use Models\RaffleDraw;
use Models\RaffleRuleTier;

/**
 * Creates and populates draw by lcs api response.
 */
class Services_Raffle_Draw_Factory
{
    private Raffle $raffle_dao;
    private Validator_Lcs_Draws $lcs_draw_validator;
    private RaffleRuleTier $tier_dao;
    private Services_Raffle_Prize_Factory $prize_factory;
    private Currency $currency_dao;
    private RaffleDraw $draw_dao;

    public function __construct(
        Raffle $raffle,
        RaffleRuleTier $tier,
        Currency $currency,
        Validator_Lcs_Draws $lcs_draw_validator,
        Services_Raffle_Prize_Factory $prize_factory,
        RaffleDraw $draw
    ) {
        $this->raffle_dao = $raffle;
        $this->tier_dao = $tier;
        $this->currency_dao = $currency;
        $this->lcs_draw_validator = $lcs_draw_validator;
        $this->prize_factory = $prize_factory;
        $this->draw_dao = $draw;
    }

    /**
     * Creates the whole draw data, including prizes and tiers.
     *
     * @param array $lcs_draw
     * @param string $raffle_slug
     *
     * @return RaffleDraw
     * @throws Throwable
     */
    public function create_from_lcs_data(array $lcs_draw, string $raffle_slug): RaffleDraw
    {
        $raffle = $this->get_and_verify_raffle($raffle_slug);
        $draw = $this->verify_and_create_draw_from_lcs_data($lcs_draw, $raffle);
        $this->prize_factory->create_from_lcs_data($lcs_draw['lottery_prizes'], $raffle_slug, $draw->id);

        $draw->reload();

        return $draw;
    }

    private function get_and_verify_raffle(string $slug): Raffle
    {
        return $this->raffle_dao->get_by_slug_with_currency_and_rule($slug);
    }

    private function verify_and_create_draw_from_lcs_data(array $lcs_draw, Raffle $raffle): RaffleDraw
    {
        $this->lcs_draw_validator->validate($lcs_draw);

        $draw = new RaffleDraw($lcs_draw);
        $draw->raffle_id = $raffle->id;
        $draw->raffle_rule_id = $raffle->raffle_rule_id;
        $draw->currency_id = $this->get_currency_by_code($lcs_draw['currency_code'])->id;
        $draw->numbers = json_encode($lcs_draw['numbers']);

        $this->draw_dao->store($draw);

        return $draw;
    }

    private function get_currency_by_code(string $currency_code): Currency
    {
        return $this->currency_dao->get_by_code($currency_code);
    }
}
