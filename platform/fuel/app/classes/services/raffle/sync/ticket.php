<?php

use Wrappers\Db;
use Models\RaffleDraw;
use Models\WhitelabelRaffleTicket;
use Repositories\Orm\RaffleRepository;
use Repositories\Orm\TicketRepository;
use Modules\Account\Reward\RewardDispatcher;

/**
 * Synchronizes LCS tickets statuses with WL DB state.
 */
class Services_Raffle_Sync_Ticket
{
    private Services_Raffle_Status_Verifier $raffle_verifier;
    private WhitelabelRaffleTicket $ticket_dao;
    private Services_Raffle_Logger $logger;
    private Services_Lcs_Raffle_Ticket_Get_Contract $tickets_api;
    private Services_Raffle_Sync_Ticket_Updater $ticket_updater;
    private RewardDispatcher $reward_dispatcher;
    private Db $db;
    private RaffleDraw $draw_dao;
    private RaffleRepository $raffleRepo;
    private TicketRepository $ticketRepo;

    public function __construct(
        Services_Raffle_Status_Verifier $raffle_verifier,
        WhitelabelRaffleTicket $ticket,
        Services_Raffle_Logger $logger,
        Services_Lcs_Raffle_Ticket_Get_Contract $tickets_api,
        RewardDispatcher $reward,
        RaffleDraw $draw,
        Db $db,
        Services_Raffle_Sync_Ticket_Updater $ticket_updater,
        RaffleRepository $raffleRepo,
        TicketRepository $ticketRepo
    ) {
        $this->raffle_verifier = $raffle_verifier;
        $this->ticket_dao = $ticket;
        $this->logger = $logger;
        $this->tickets_api = $tickets_api;
        $this->ticket_updater = $ticket_updater;
        $this->reward_dispatcher = $reward;
        $this->db = $db;
        $this->draw_dao = $draw;
        $this->raffleRepo = $raffleRepo;
        $this->ticketRepo = $ticketRepo;
    }

    public function synchronize(string $raffle_slug, bool $force = false): void
    {
        if (!$force) {
            $this->raffle_verifier->getAndVerifyIsSynchronizeAble($raffle_slug);
        }

        $raffle = $this->raffleRepo->getBySlug($raffle_slug);

        if ($this->has_unsynced_draws($raffle_slug) === false) {
            return;
        }

        // for raffle closed, until pool is empty
        $unsynchronized_tickets = $this->ticket_dao->get_all_unsynchronized_tickets($raffle_slug);
        if (empty($unsynchronized_tickets)) {
            return;
        }
        $lcs_tickets = $this->get_lcs_tickets($raffle_slug, 'closed', $unsynchronized_tickets);
        if (empty($lcs_tickets)) {
            throw new RuntimeException('Unable to fetch LCS`s tickets!');
        }

        $this->mark_related_draws_as_synced($raffle_slug, $lcs_tickets);

        $this->db->start_transaction();
        try {
            foreach ($unsynchronized_tickets as $ticket) {
                $lcs_ticket_data = $lcs_tickets[$ticket->uuid];

                if ($lcs_ticket_data['status'] === Helpers_General::TICKET_STATUS_PENDING) {
                    continue;
                }

                $this->ticket_updater->update_ticket_by_lcs_data(
                    $ticket,
                    $lcs_ticket_data,
                    function (WhitelabelRaffleTicket $ticket) {
                        $this->reward_dispatcher->dispatchTicket($ticket);
                    }
                );
            }

            $this->ticketRepo->saveManyAndFlush(...$unsynchronized_tickets);

            if ($this->reward_dispatcher->is_enqueued()) {
                $this->reward_dispatcher->dispatch();
            }
        } catch (Throwable $exception) {
            $this->db->rollback_transaction();
            $this->mark_related_draws_as_not_synced($raffle_slug, $lcs_tickets);
            $this->logger->log_error($raffle_slug, $exception->getMessage());
            throw $exception;
        }
        $this->db->commit_transaction();

        $this->raffleRepo->resetDrawLinesAndDisableSellWhenSoldOut($raffle->id);
    }

    /**
     * @param string $raffle_slug
     * @param string $raffle_type
     * @param array $unsynchronized_tickets
     *
     * @return array - we map ticket's uuids to indexes, for faster array checkout
     */
    private function get_lcs_tickets(string $raffle_slug, string $raffle_type, array $unsynchronized_tickets): array
    {
        $payload['uuids'] = array_map(function (WhitelabelRaffleTicket $ticket) {
            return $ticket->uuid;
        }, $unsynchronized_tickets);
        $result = $this->tickets_api->request($payload, $raffle_slug, $raffle_type)->get_body();

        if (empty($result)) {
            return [];
        }

        $uuid_as_key = [];
        foreach ($result as $ticket_data) {
            if (empty($ticket_data['uuid'])) {
                throw new InvalidArgumentException(sprintf('Missing UUID in ticket #%s from LCS!', $ticket_data['token']));
            }
            $uuid_as_key[$ticket_data['uuid']] = $ticket_data;
        }
        return $uuid_as_key;
    }

    /**
     * We have to mark related draws as synced/unsynced (out of the transaction)
     * to avoid multiple synchronization in the same time.
     * It is very important to rollback this marker after main transaction failure.
     *
     * @param string $raffle_slug
     * @param array $lcs_tickets
     */
    private function mark_related_draws_as_synced(string $raffle_slug, array $lcs_tickets)
    {
        $dates = array_unique(array_map(function (array $ticket) {
            return $ticket['draw_date'];
        }, $lcs_tickets));
        $this->draw_dao->mark_draw_sync($raffle_slug, array_values($dates), true);
    }

    private function mark_related_draws_as_not_synced(string $raffle_slug, array $lcs_tickets)
    {
        $dates = array_unique(array_map(function (array $ticket) {
            return $ticket['draw_date'];
        }, $lcs_tickets));
        $this->draw_dao->mark_draw_sync($raffle_slug, array_values($dates), false);
    }

    private function has_unsynced_draws(string $raffle_slug): bool
    {
        return $this->draw_dao->count_unsynced_draws($raffle_slug) > 0;
    }
}
