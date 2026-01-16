<?php

final class Database_Service_Lottery
{
    /**
     * @param Closure $fetch_lotteries function (array $whitelabel): array concrete logic for retrieval of lotteries.
     */
    public static function get_lotteries(Closure $fetch_lotteries): array
    {
        $whitelabel = Model_Whitelabel::get_by_domain(Lotto_Helper::getWhitelabelDomainFromUrl());
        $whitelabel_not_found = $whitelabel === null;
        if ($whitelabel_not_found) {
            return [];
        }

        return  $fetch_lotteries($whitelabel);
    }
}
