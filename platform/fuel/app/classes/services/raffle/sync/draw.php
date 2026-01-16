<?php

use Repositories\Orm\RaffleRepository;
use Wrappers\Db;

/**
 * Synchronizes LCS draw data with WL DB state.
 */
class Services_Raffle_Sync_Draw
{
    private Services_Raffle_Draw_Factory $draw_factory;
    private Services_Raffle_Sync_Draw_Lister $draws_lister;
    private Services_Raffle_Logger $logger;
    private Db $db;
    private RaffleRepository $raffleRepo;
    private Services_Raffle_Status_Verifier $raffle_verifier;

    public function __construct(
        Services_Raffle_Status_Verifier $raffle_verifier,
        Services_Raffle_Draw_Factory $draw_factory,
        Services_Raffle_Sync_Draw_Lister $draws_lister,
        Services_Raffle_Logger $logger,
        RaffleRepository $raffleRepo,
        Db $db
    ) {
        $this->draw_factory = $draw_factory;
        $this->draws_lister = $draws_lister;
        $this->logger = $logger;
        $this->db = $db;
        $this->raffleRepo = $raffleRepo;
        $this->raffle_verifier = $raffle_verifier;
    }

    public function synchronize(string $raffle_slug, bool $force = false): void
    {
        if (!$force) {
            $this->raffle_verifier->getAndVerifyIsSynchronizeAble($raffle_slug);
        }

        $lcs_draws = $this->draws_lister->get_all_lcs_unsynchronized_draws(
            $raffle_slug,
            'closed' # todo ST: 2 - Remove closed type in request, due it was never, ever used and won't be
        );

        if (empty($lcs_draws)) {
            return;
        }

        $this->db->start_transaction();
        try {
            foreach ($lcs_draws as $lcs_draw) {
                $draw = $this->draw_factory->create_from_lcs_data($lcs_draw, $raffle_slug);
                $this->raffleRepo->updateRaffleDetailsByDraw($raffle_slug, $draw);

                $this->logger->log_success(
                    $raffle_slug,
                    sprintf('Created new draw #%d for Raffle %s from %s', $draw->draw_no, $raffle_slug, $draw->date->format('mysql'))
                );
            }
        } catch (Throwable $exception) {
            $this->db->rollback_transaction();
            $this->logger->log_error($raffle_slug, $exception->getMessage());
            throw $exception;
        }
        $this->db->commit_transaction();
    }
}
