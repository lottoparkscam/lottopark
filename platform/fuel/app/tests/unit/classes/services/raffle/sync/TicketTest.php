<?php

namespace Tests\Unit\Classes\Services\Raffle\Sync;

use Exception;
use Helpers_General;
use Modules\Account\Reward\RewardDispatcher;
use Repositories\Orm\RaffleRepository;
use Repositories\Orm\TicketRepository;
use Services_Lcs_Raffle_Ticket_Get_Contract;
use Services_Raffle_Logger;
use Services_Raffle_Status_Verifier;
use Models\WhitelabelRaffleTicket;
use Services_Raffle_Sync_Ticket;
use Models\RaffleDraw;
use Services_Raffle_Sync_Ticket_Updater;
use Test_Unit;
use Wrappers\Db;

final class TicketTest extends Test_Unit
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
    private Services_Raffle_Sync_Ticket $service;
    private TicketRepository $ticketRepo;

    public function setUp(): void
    {
        parent::setUp();

        $this->raffle_verifier = $this->createMock(Services_Raffle_Status_Verifier::class);
        $this->ticket_dao = $this->createMock(WhitelabelRaffleTicket::class);
        $this->logger = $this->createMock(Services_Raffle_Logger::class);
        $this->tickets_api = $this->createMock(Services_Lcs_Raffle_Ticket_Get_Contract::class);
        $this->ticket_updater = $this->createMock(Services_Raffle_Sync_Ticket_Updater::class);
        $this->reward_dispatcher = $this->createMock(RewardDispatcher::class);
        $this->db = $this->createMock(Db::class);
        $this->draw_dao = $this->createMock(RaffleDraw::class);
        $this->raffleRepo = $this->createMock(RaffleRepository::class);
        $this->ticketRepo = $this->createMock(TicketRepository::class);

        $this->service = new Services_Raffle_Sync_Ticket(
            $this->raffle_verifier,
            $this->ticket_dao,
            $this->logger,
            $this->tickets_api,
            $this->reward_dispatcher,
            $this->draw_dao,
            $this->db,
            $this->ticket_updater,
            $this->raffleRepo,
            $this->ticketRepo
        );
    }

    /** @test */
    public function synchronize_WithForce_WillNotCheckRaffle(): void
    {
        // Given
        $force = true;
        $raffleSlug = 'some-slg';

        $this->raffle_verifier
            ->expects($this->never())
            ->method('getAndVerifyIsSynchronizeAble');

        // When
        $this->service->synchronize($raffleSlug, $force);
    }

    /** @test */
    public function synchronize_WithNotForce_WillCheckRaffle(): void
    {
        // Given
        $force = false;
        $raffleSlug = 'some-slg';

        $this->raffle_verifier
            ->expects($this->once())
            ->method('getAndVerifyIsSynchronizeAble')
            ->with($raffleSlug);

        // When
        $this->service->synchronize($raffleSlug, $force);
    }

    /** @test */
    public function synchronize_HasUnsyncedDraws_Skips(): void
    {
        // Given
        $raffle_slug = 'fake_slg';
        $this->draw_dao
            ->expects($this->once())
            ->method('count_unsynced_draws')
            ->with($raffle_slug)
            ->willReturn(0);

        $this->ticket_dao
            ->expects($this->never())
            ->method('get_all_unsynchronized_tickets')
            ->with($raffle_slug);

        // When
        $this->service->synchronize($raffle_slug);
    }

    /** @test */
    public function synchronize_HasTicketsToProcess_MarkAsSyncedBeforeMainTransaction(): void
    {
        // Given
        $tickets = [
            new WhitelabelRaffleTicket(['uuid' => '123', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS]),
            new WhitelabelRaffleTicket(['uuid' => '1234', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS]),
        ];
        $raffle_slug = 'fake_slg';

        $raffle = $this->get_raffle(['slug' => $raffle_slug]);
        $this->raffleRepo
            ->expects($this->once())
            ->method('getBySlug')
            ->with($raffle_slug)
            ->willReturn($raffle);

        $this->draw_dao
            ->expects($this->once())
            ->method('count_unsynced_draws')
            ->with($raffle_slug)
            ->willReturn(3);

        $this->draw_dao
            ->expects($this->once())
            ->method('mark_draw_sync')
            ->with($raffle_slug, ['2020-11-30 12:50:00', '2020-11-29 12:55:00'], true);

        $this->ticket_dao
            ->expects($this->once())
            ->method('get_all_unsynchronized_tickets')
            ->with($raffle_slug)
            ->willReturn($tickets);

        $this->ticketRepo
            ->expects($this->once())
            ->method('saveManyAndFlush')
            ->with(...$tickets);

        $this->tickets_api
            ->expects($this->once())
            ->method('request')
            ->withAnyParameters()
            ->willReturn($this->mock_lcs_response([
                ['draw_date' => '2020-11-30 12:50:00', 'uuid' => '123', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS],
                ['draw_date' => '2020-11-29 12:55:00', 'uuid' => '1234', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS],
            ]));

        $this->service->synchronize($raffle_slug);
    }

    /** @test */
    public function synchronize_HasTicketsToProcessButExceptionOccurs_MarkAsSyncedBeforeMainTransactionAndRollbackOnFail(): void
    {
        // Expects
        $this->expectException(Exception::class);

        // Given
        $tickets = [
            new WhitelabelRaffleTicket(['uuid' => '123', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS]),
            new WhitelabelRaffleTicket(['uuid' => '1234', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS]),
        ];

        $raffle_slug = 'fake_slg';
        $this->draw_dao
            ->expects($this->once())
            ->method('count_unsynced_draws')
            ->with($raffle_slug)
            ->willReturn(3);

        $this->draw_dao
            ->expects($this->exactly(2))
            ->method('mark_draw_sync')
            ->withConsecutive(
                [$raffle_slug, ['2020-11-30 12:50:00', '2020-11-29 12:55:00'], true],
                [$raffle_slug, ['2020-11-30 12:50:00', '2020-11-29 12:55:00'], false]
            );

        $this->ticket_dao
            ->expects($this->once())
            ->method('get_all_unsynchronized_tickets')
            ->with($raffle_slug)
            ->willReturn($tickets);

        $this->ticket_updater->method('update_ticket_by_lcs_data')->withAnyParameters()->willThrowException(new Exception());

        $this->tickets_api
            ->expects($this->once())
            ->method('request')->withAnyParameters()
            ->willReturn($this->mock_lcs_response([
                ['draw_date' => '2020-11-30 12:50:00', 'uuid' => '123', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS],
                ['draw_date' => '2020-11-29 12:55:00', 'uuid' => '1234', 'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS],
            ]));

        $this->service->synchronize($raffle_slug);
    }
}
