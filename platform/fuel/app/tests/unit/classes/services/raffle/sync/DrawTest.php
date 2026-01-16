<?php

namespace Tests\Unit\Classes\Services\Raffle\Sync;

use Fuel\Core\Date;
use Repositories\Orm\RaffleRepository;
use Wrappers\Db;
use Test_Unit;
use Services_Raffle_Sync_Draw_Lister;
use Services_Raffle_Draw_Factory;
use Services_Raffle_Logger;
use Services_Raffle_Status_Verifier;
use Models\Raffle;
use Services_Raffle_Sync_Draw;
use Models\RaffleDraw;
use Exception;

final class DrawTest extends Test_Unit
{
    private Services_Raffle_Sync_Draw_Lister $draw_lister;
    private Services_Raffle_Draw_Factory $draw_factory;
    private Services_Raffle_Logger $logger;
    private Db $db;
    private RaffleRepository $raffleRepo;
    private Services_Raffle_Status_Verifier $raffle_verifier;
    private Raffle $raffle;

    private $valid_lcs_draws_response = [];

    /** @var Services_Raffle_Sync_Draw */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->raffle_verifier = $this->createMock(Services_Raffle_Status_Verifier::class);
        $this->draw_lister = $this->createMock(Services_Raffle_Sync_Draw_Lister::class);
        $this->logger = $this->createMock(Services_Raffle_Logger::class);
        $this->draw_factory = $this->createMock(Services_Raffle_Draw_Factory::class);
        $this->raffleRepo = $this->createMock(RaffleRepository::class);
        $this->db = $this->createMock(Db::class);

        $raffle = new Raffle();
        $raffle->slug = 'gg-world-raffle';

        $this->raffle = $raffle;

        $this->valid_lcs_draws_response = json_decode(
            file_get_contents(
                APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, 'tests\data\lcs\draws_to_sync_response.json')
            ),
            true
        );

        $this->service = new Services_Raffle_Sync_Draw(
            $this->raffle_verifier,
            $this->draw_factory,
            $this->draw_lister,
            $this->logger,
            $this->raffleRepo,
            $this->db
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
    public function synchronize__with_not_force__will_check_raffle(): void
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
    public function synchronize_GetSllLcsUnsynchronizedDrawsNotFound_Breaks(): void
    {
        // Given
        $lister_response = [];

        $this->db->expects($this->never())->method('start_transaction');

        $this->draw_lister
            ->expects($this->once())
            ->method('get_all_lcs_unsynchronized_draws')
            ->with($this->raffle->slug, 'closed')
            ->willReturn($lister_response);

        $this->db->expects($this->never())->method('start_transaction');

        // When
        $this->service->synchronize($this->raffle->slug, 'closed');
    }

    /** @test */
    public function synchronize_GetAllLcsUnsynchronizedDrawsFound_CreatesDrawUpdatesRaffle(): void
    {
        // Given
        $lister_response = $this->valid_lcs_draws_response;
        $this->assertNotEmpty($lister_response);

        $this->db->expects($this->once())->method('start_transaction');
        $this->db->expects($this->once())->method('commit_transaction');
        $this->db->expects($this->never())->method('rollback_transaction');

        $this->draw_lister->expects($this->once())
            ->method('get_all_lcs_unsynchronized_draws')
            ->with($this->raffle->slug, 'closed')
            ->willReturn($lister_response);

        $new_draw = new RaffleDraw();
        $new_draw->date = Date::forge()->format('mysql');

        $this->draw_factory->expects($this->exactly(sizeof($lister_response)))
            ->method('create_from_lcs_data')
            ->withConsecutive(
                [$lister_response[0], $this->raffle->slug],
                [$lister_response[1], $this->raffle->slug],
                [$lister_response[2], $this->raffle->slug],
            )
            ->willReturnOnConsecutiveCalls(
                $new_draw,
                $new_draw,
                $new_draw,
            );

        $this->raffleRepo->expects($this->exactly(sizeof($lister_response)))
            ->method('updateRaffleDetailsByDraw')
            ->withConsecutive(
                [$this->raffle->slug, $new_draw],
                [$this->raffle->slug, $new_draw],
                [$this->raffle->slug, $new_draw],
            );

        $this->logger->expects($this->exactly(sizeof($lister_response)))
            ->method('log_success');

        // When
        $this->service->synchronize($this->raffle->slug, 'closed');
    }

    /** @test */
    public function synchronizeTransaction_AnyException_ThrowsExceptionRollbacksTransaction(): void
    {
        // Expects
        $this->expectException(Exception::class);

        // Given
        $lister_response = $this->valid_lcs_draws_response;

        $this->db->expects($this->once())->method('start_transaction');
        $this->db->expects($this->never())->method('commit_transaction');
        $this->db->expects($this->once())->method('rollback_transaction');

        $this->draw_lister
            ->expects($this->once())
            ->method('get_all_lcs_unsynchronized_draws')
            ->with($this->raffle->slug, 'closed')
            ->willReturn($lister_response);

        $this->draw_factory
            ->expects($this->once())
            ->method('create_from_lcs_data')
            ->willThrowException(new Exception('Same error'));

        $this->raffleRepo
            ->expects($this->never())
            ->method('updateRaffleDetailsByDraw');

        $this->logger
            ->expects($this->once())
            ->method('log_error')
            ->with($this->raffle->slug, 'Same error');

        // When
        $this->service->synchronize($this->raffle->slug, 'closed');
    }
}
