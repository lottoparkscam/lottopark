<?php


abstract class Task_Lotterycentralserver_Fetch_Game extends Task_Lotterycentralserver_Fetch_Task
{
    use Model_Traits_Set_Jackpot; // we need this to compare jackpots

    /**
     * There is no difference between LCS and whitelotto.
     */
    const UP_TO_DATE = 0;

    /**
     * Jackpot was changed in LCS
     * NOTE: it is bitwise constant, use with AND $result_code & JACKPOT_DIFFER
     */
    const JACKPOT_DIFFER = 0x01;

    /**
     * Draw date or time was changed in LCS - draw was executed.
     * NOTE: it is bitwise constant, use with AND $result_code & JACKPOT_DIFFER
     */
    const DRAW_DATE_DIFFER = 0x02;
}