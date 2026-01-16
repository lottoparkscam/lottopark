<?php

namespace Tests\Unit\Classes\Model\Whitelabel\Raffle\Orm;

use Models\WhitelabelRaffleTicket;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeType;
use Test_Unit;

final class TicketTest extends Test_Unit
{
    /** @test */
    public function canBePaidOut_HasOnlyCashTypePrizes_ReturnsTrue(): void
    {
        // Given
        $ticket = new WhitelabelRaffleTicket();
        $expected = true;

        // When
        $line = $this->createMock(WhitelabelRaffleTicketLine::class);
        $line
            ->expects($this->once())
            ->method('prizeType')
            ->willReturn(PrizeType::CASH());
        $ticket->lines = [$line];

        // Then
        $this->assertSame($expected, $ticket->can_be_paid_out);
    }

    /** @test */
    public function canBePaidOut_HasNotOnlyCashTypePrizes_ReturnsFalse(): void
    {
        // Given
        $ticket = new WhitelabelRaffleTicket();
        $expected = false;

        // When
        $line1 = $this->createMock(WhitelabelRaffleTicketLine::class);
        $line1
            ->expects($this->once())
            ->method('prizeType')
            ->willReturn(PrizeType::CASH());
        $line2 = $this->createMock(WhitelabelRaffleTicketLine::class);
        $line2
            ->expects($this->once())
            ->method('prizeType')
            ->willReturn(PrizeType::TICKET());
        $ticket->lines = [$line1, $line2];

        // Then
        $this->assertSame($expected, $ticket->can_be_paid_out);
    }
}
