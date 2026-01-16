<?php

namespace Unit\Classes\Modules\Account\Reward\Strategy;

use Models\RafflePrize;
use Models\RaffleRuleTier;
use Models\RaffleRuleTierInKindPrize;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\Strategy\PrizeInKindDispatcher;
use Modules\Account\UserPopupQueueDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;

class PrizeInKindDispatcherTest extends Test_Unit
{
    private PrizeInKindDispatcher $service;
    private WhitelabelRaffleTicketLine $valid_line;
    private UserPopupQueueDecorator $queue_popup;

    public function setUp(): void
    {
        parent::setUp();

        $this->queue_popup = $this->createMock(UserPopupQueueDecorator::class);
        $this->service = new PrizeInKindDispatcher($this->queue_popup);

        $ticket = $this->get_ticket();
        $line = $ticket->lines[0];
        $tier = new RaffleRuleTier();
        $raffle_prize = new RafflePrize();
        $raffle_prize->tier = $tier;
        $prize_in_kind = new RaffleRuleTierInKindPrize();
        $prize_in_kind->type = 'prize-in-kind';
        $tier->tier_prize_in_kind = $prize_in_kind;
        $line->raffle_prize = $raffle_prize;
        $this->valid_line = $line;
    }

    /** @test */
    public function dispatchPrize__ticket_is_not_prize_in_kind_type__skips(): void
    {
        // Given
        /** @var WhitelabelRaffleTicketLine|MockObject $line */
        $line = $this->createMock(WhitelabelRaffleTicketLine::class);

        $line
            ->expects($this->once())
            ->method('prizeType')
            ->willReturn(PrizeType::TICKET());

        $this->queue_popup
            ->expects($this->never())
            ->method('once');

        // When
        $this->service->dispatchPrize($line);
    }

    /**
     * @test
     */
    public function dispatchPrize__ticket_has_prize_in_kind__enqueues_popup(): void
    {
        // Given
        $line = $this->valid_line;
        $ticket = $line->ticket;
        $ticket->whitelabel_user_id = 22;
        $prize_in_kind = $line->raffle_prize->tier->tier_prize_in_kind;
        $prize_in_kind->id = 69;
        $prize_in_kind->name = 'Tesla';

        # popup
        $expected_unique_queue_id = 'a:2:{i:0;i:22;i:1;i:69;}';
        $expected_popup_title = $prize_in_kind->name;
        $expected_popup_message = 'You have won Tesla!';

        $this->queue_popup
            ->expects($this->once())
            ->method('once')
            ->with($expected_unique_queue_id)
            ->willReturnSelf();

        $this->queue_popup
            ->expects($this->once())
            ->method('pushMessage')
            ->with(
                $ticket->whitelabel_id,
                $ticket->whitelabel_user_id,
                $expected_popup_title,
                $expected_popup_message
            );

        // When
        $this->service->dispatchPrize($line);

        // Then
        $this->assertTrue(PrizeType::IN_KIND()->equals($line->prizeType()));
    }
}
