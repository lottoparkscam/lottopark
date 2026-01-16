<?php

use Fuel\Core\Database_Query_Builder_Update;

/**
 * Allow ticket/line model to set it's prizes.
 * NOTE: INCLUDES Calculates Prizes trait.
 */
trait Model_Traits_Set_Prizes
{
    use Model_Traits_Calculates_Prizes;

    /**
     * Build base query for set winning ticket/line.
     * BASE METHOD.
     *
     * @param string $prize_local
     * @param Model_Lottery_Provider $provider
     * @param array $additional_fields additional fields to set, which differ between implementations.
     * @return Database_Query_Builder_Update query.
     */
    private static function set_winning_base_(
        string $prize_local,
        string $prize_usd,
        Model_Lottery_Provider $provider,
        bool $payout,
        array $additional_fields = []
    ): Database_Query_Builder_Update {
        $merged_fields = [
            'status' => Helpers_General::TICKET_STATUS_WIN,
            'prize_local' => $prize_local,
            'prize_net_local' => self::calculate_prize_net($prize_local, $provider),
            'prize_usd' => $prize_usd,
            'prize_net_usd' => self::calculate_prize_net($prize_usd, $provider),
            'payout' => $payout,
        ]
            +   $additional_fields;
        return DB::update(self::$_table_name)
            ->set($merged_fields);
    }

    /**
     * Fill manager fields in query.
     * BASE METHOD.
     *
     * @param Database_Query_Builder_Update $query
     * @param string $prize_manager
     * @param Model_Lottery_Provider $provider
     * @param array $additional_fields additional fields to set, which differ between implementations.
     * @return void.
     */
    private static function set_winning_manager_(
        Database_Query_Builder_Update &$query,
        string $prize_manager,
        Model_Lottery_Provider $provider,
        array $additional_fields = []
    ): void {
        $query
            ->value('prize_manager', $prize_manager)
            ->value('prize_net_manager', self::calculate_prize_net($prize_manager, $provider));

        foreach ($additional_fields as $name => $value) {
            $query->value($name, $value);
        }
    }

    /**
     * Finish set winning query.
     * BASE METHOD.
     *
     * @param Database_Query_Builder_Update $query
     * @param string $prize_user
     * @param array $ids
     * @param Model_Lottery_Provider $provider  
     * @param array $additional_fields additional fields to set, which differ between implementations.
     * @return Database_Query_Builder_Update
     */
    private static function set_winning_finish_(
        Database_Query_Builder_Update &$query,
        string $prize_user,
        array $ids,
        Model_Lottery_Provider $provider,
        array $additional_fields = []
    ): Database_Query_Builder_Update {
        $query
            ->value('prize', $prize_user)
            ->value('prize_net', self::calculate_prize_net($prize_user, $provider))
            ->where('id', 'IN', $ids);

        foreach ($additional_fields as $name => $value) {
            $query->value($name, $value);
        }

        return $query;
    }
}
