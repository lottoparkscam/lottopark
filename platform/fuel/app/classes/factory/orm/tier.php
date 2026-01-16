<?php

use Models\Raffle;
use Models\RaffleRule;
use Models\RaffleRuleTier;
use Classes\Orm\AbstractOrmModel;
use Fuel\Tasks\Factory\Utils\Faker;

/**
 * @deprecated - use new fixtures instead
 *
 * Class Factory_Orm_Tier
 * @Author Sebastian Twaróg <sebastian.twarog@gg.international>
 *
 * @FeatureTest Tests_Feature_Classes_Factory_Orm_Tier
 *
 * @method static RaffleRuleTier[] create(int $amount, array $props = [], ?Closure $closure = null)
 * @method static RaffleRuleTier[] make(int $amount, array $props = [], ?Closure $closure = null)
 */
class Factory_Orm_Tier extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $rand_type = function (): int {
            $types = [
                Model_Lottery_Type_Data::FIXED,
                Model_Lottery_Type_Data::PARIMUTUEL,
                Model_Lottery_Type_Data::JACKPOT,
            ];
            return $types[array_rand($types)];
        };
        $matches = [Faker::forge()->numberBetween(1, 5), Faker::forge()->numberBetween(5, 20)];
        $defaults = [
            'raffle_rule_id' => null,
            'currency_id' => null,

            'slug' => 'raffle:' . implode('_', $matches),
            'matches' => json_encode($matches),
            'prize_type' => $rand_type(),
            'prize_fund_percent' => Faker::forge()->numberBetween(5, 20),
            'odds' => Faker::forge()->randomNumber(4),
            'prize' => Faker::forge()->randomFloat(2, 100, 10000),
            'is_main_prize' => Faker::forge()->boolean(20)
        ];
        $this->props = array_merge($defaults, $props);
    }

    /**
     * @deprecated - use new fixtures instead
     *
     * @param Raffle $raffle
     * @return $this
     */
    public function for_raffle(Raffle $raffle): self
    {
        $this->props['raffle_rule_id'] = $raffle->raffle_rule_id;
        $this->props['currency_id'] = $raffle->currency_id;
        return $this;
    }

    /**
     * @deprecated - use new fixtures instead
     *
     * @param RaffleRule $rule
     * @return $this
     */
    public function for_rule(RaffleRule $rule): self
    {
        $this->props['raffle_rule_id'] = $rule->id;
        $this->props['currency_id'] = $rule->raffle->currency_id;
        return $this;
    }
    /**
     * @deprecated - use new fixtures instead
     *
     * @param bool $save
     *
     * @return RaffleRuleTier
     * @throws Throwable
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        if ($save) {
            $rule_tier = RaffleRuleTier::first_or_create($this->props, [
                ['slug', '=', $this->props['slug']],
            ]);
        } else {
            $rule_tier = new RaffleRuleTier($this->props);
        }

        return $rule_tier;
    }
}
