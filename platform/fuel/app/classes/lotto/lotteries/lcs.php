<?php

use Oil\Refine;

/**
 * @deprecated need to be overhauled
 */
abstract class Lotto_Lotteries_LCS extends Lotto_Lotteries_Feed
{
    public function get_results(): void
    {
        Refine::run("Lcs:update_draw_data", [
            'slug' => $this->lottery_slug
        ]);
    }
}
