<?php

interface Task_Lotterycentralserver_Chain_Config_Ticket_Batch
{

    /**
     * How many tickets can be sent to lcs.
     * LCS has set timeout 1s per slip with communication to Ltech
     * In database at this moment the biggest amount of slips per ticket is 5
     * It means when we send in one batch 5 ticket to LCS, we have 25 slips per request
     * It means LCS has 25s in sum of each request to process
     * Our default CURL settings is max 25s timeout per request, so we should not increase this tickets count
     * Use more iterations instead
     *
     * @var int
     */
    const TICKET_COUNT_DEFAULT = 2;

    /**
     * How many lines ticket can have.
     *
     * @var int
     */
    const TICKET_MAX_LINES_DEFAULT = 25;

    /**
     * How many lines can be read from database
     * Cannot be less than TICKET_COUNT_DEFAULT * TICKET_MAX_LINES_DEFAULT
     * In other case some slips could not be processed
     *
     * @var int
     */
    const BATCH_SIZE_DEFAULT = 50; // self::TICKET_COUNT_DEFAULT * self::TICKET_MAX_LINES_DEFAULT

    /**
     * Lcs ticket retries - how many times ticket insertion will be retried in case of failure.
     *
     * @var int
     */
    const LCS_TICKET_RETRIES_DEFAULT = 10;

    /**
     * How many batches can be done per one task.
     *
     * @var int
     */
    const MAX_ITERATION_DEFAULT = 7;

    /**
     * Batch size config per lottery.
     *
     * @var array
     */
    const PER_LOTTERY = [
    ];
}
