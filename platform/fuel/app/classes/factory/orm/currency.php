<?php

use Models\Currency;
use Classes\Orm\AbstractOrmModel;
use Fuel\Tasks\Factory\Utils\Faker;

/**
 * @deprecated - use new fixtures instead
 *
 * Class Factory_Orm_Currency
 * @Author Sebastian Twaróg <sebastian.twarog@gg.international>
 *
 * @UnitTest missing
 * @FeatureTest Tests_Feature_Classes_Factory_Orm_Currency
 * @E2ETest not required
 *
 * @method static Currency[] create(int $amount, array $props = [], ?Closure $closure = null)
 * @method static Currency[] make(int $amount, array $props = [], ?Closure $closure = null)
 */
class Factory_Orm_Currency extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $defaults = [
            'code' => Faker::forge()->countryISOAlpha3(),
            'rate' => Faker::forge()->randomFloat(1, 1, 10),
        ];
        $this->props = array_merge($defaults, $props);
    }

    public static function as_usd(): self
    {
        return new self([
            'code' => 'USD',
            'rate' => 1,
        ]);
    }

    /**
     * @param bool $save
     *
     * @return Currency
     * @throws Exception
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        if ($save) {
            $currency = Currency::first_or_create($this->props, [
                'code', '=', $this->props['code']
            ]);
        } else {
            $currency = new Currency($this->props);
        }

        return $currency;
    }
}
