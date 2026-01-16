<?php

namespace Tests\Feature\Classes\Repositories\Orm;

use Models\Raffle;
use Repositories\Orm\RaffleRepository;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleTicketFixture;

/**
 * @group raffle
 * @group skipped
 */
final class RaffleRepositoryTest extends Test_Feature
{
    private RaffleRepository $repo;
    private RaffleFixture $raffleFixture;
    private RaffleTicketFixture $ticketFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->container->get(RaffleRepository::class);
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
        $this->ticketFixture = $this->container->get(RaffleTicketFixture::class);
    }

    /** @test */
    public function resetDrawLinesAndDisableSellWhenSoldOut(): void
    {
        // Given raffle with is_sell_enabled = false
        /** @var RaffleFixture $fixture */
        $fixture = $this->container->get(RaffleFixture::class);
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with('basic')->makeOne();

        // And lines count (half of the max bets)
        $linesCount = $raffle->max_bets / 2;

        $tickets = $this->ticketFixture->generateTickets(
            $raffle,
            $this->ticketFixture::PENDING,
            $this->ticketFixture::RANDOM,
            $linesCount
        );

        $this->ticketFixture->getPersistence()->persist(...$tickets);
        Raffle::flush_cache();

        // When reset draw lines
        $this->repo->resetDrawLinesAndDisableSellWhenSoldOut($raffle->id);

        // Then lottery should be playable
        // And lines_count should be equal to ticket lines
        /** @var Raffle $fresh */
        $fresh = $this->repo->getById($raffle->id);

        // Then is_sell_enabled should be true
        $this->assertSame($linesCount, $fresh->draw_lines_count);
    }
}
