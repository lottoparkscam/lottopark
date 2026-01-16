<?php

/**
 * @property string $max_payout unsigned decimal(13,2) maximal prize (inclusive), which should be paid out automatically
 * @property-read string $timezone
 * @property-read float $tax_min
 * @property-read float $tax
 */
class Model_Lottery_Provider extends \Fuel\Core\Model_Crud
{
    use Model_Traits_Last_For_Lottery;

    // TODO: {Vordis 2019-10-24 15:33:35} started as links (for readability), but target position of those consts is here
    /**
     * @var int
     */
    const IMVALAP = Helpers_General::PROVIDER_IMVALAP;
    /**
     * @var int
     */
    const LOTTORISQ = Helpers_General::PROVIDER_LOTTORISQ;
    /**
     * @var int
     */
    const NONE = Helpers_General::PROVIDER_NONE;
    /**
     * @var int
     */
    const LOTTERY_CENTRAL_SERVER = Helpers_General::PROVIDER_LOTTERY_CENTRAL_SERVER;
    /**
     * @var int
     */
    const FEED = Helpers_General::PROVIDER_FEED;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottery_provider';

    /**
     *
     * @var array
     */
    private $json_fields = [
        'closing_times'
    ];

    # ACCESSORS
    public function __get($property)
    {
        $field_value = parent::__get($property);
        $should_cast_into_array = in_array($property, $this->json_fields);
        if ($should_cast_into_array) {
            return json_decode($field_value, true);
        };

        return $field_value;
    }
}
