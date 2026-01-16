<?php

/** Parent for batch chain task with lottery. */
abstract class Task_Lotterycentralserver_Chain_Lottery_Batch extends Task_Lotterycentralserver_Chain_Task
{
    /**
     * True if this task should be executed in database transaction.
     * Default false.
     *
     *
     * @var boolean
     */
    protected $in_transaction = false; // this task works in batches, which shouldn't be discarded on error 

    /**
     * lottery model
     *
     * @var Model_Lottery
     */
    protected $lottery;

    /**
     * Create new instance of chain task
     *
     * @param string $slug slug of the lottery
     *
     * @throws Throwable
     */
    public function __construct(string $slug)
    {
        parent::__construct($slug);
        try {
            $this->lottery = Model_Lottery::find_one_by('slug', $slug);
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                "Lottery with given slug cannot be fetched from the database. " . $e->getMessage()
            );
            throw $e;
        }
    }
}