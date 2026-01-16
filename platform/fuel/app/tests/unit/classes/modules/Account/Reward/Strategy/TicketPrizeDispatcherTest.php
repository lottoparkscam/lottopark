<?php

namespace Unit\Classes\Modules\Account\Reward\Strategy;

use InvalidArgumentException;
use Models\Lottery;
use Models\WhitelabelRaffleTicketLine;
use Modules\Account\Reward\PrizeInKindSynchronizer;
use Modules\Account\Reward\PrizeType;
use Modules\Account\Reward\Strategy\TicketPrizeDispatcher;
use Modules\Account\UserPopupQueueDecorator;
use RuntimeException;
use Services\Lottery\Factory\TicketFactory;
use Test_Unit;

class TicketPrizeDispatcherTest extends Test_Unit
{
    private TicketFactory $factory;
    private PrizeInKindSynchronizer $prize_synchronizer;
    private Lottery $lottery_dao;
    private UserPopupQueueDecorator $queue_popup;
    private TicketPrizeDispatcher $service;
    private WhitelabelRaffleTicketLine $valid_line;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = $this->createMock(TicketFactory::class);
        $this->prize_synchronizer = $this->createMock(PrizeInKindSynchronizer::class);
        $this->lottery_dao = $this->createMock(Lottery::class);
        $this->queue_popup = $this->createMock(UserPopupQueueDecorator::class);

        $this->service = new TicketPrizeDispatcher(
            $this->factory,
            $this->lottery_dao,
            $this->prize_synchronizer,
            $this->queue_popup
        );

        $line = $this->get_line(PrizeType::TICKET);
        $prize_in_kind = $line->raffle_prize->tier->tier_prize_in_kind;
        $prize_in_kind->config = ['count' => 5];
        $prize_in_kind->slug = 'slug';
        $prize_in_kind->name = 'name';
        $this->valid_line = $line;
    }

    /** @test */
    public function dispatchPrize__is_not_cash_type__skips(): void
    {
        // Given
        $line = $this->get_line(PrizeType::IN_KIND);

        $this->prize_synchronizer
            ->expects($this->never())
            ->method('recalculatePrizes');

        // When
        $this->service->dispatchPrize($line);
    }

    /**
     * @test
     */
    public function dispatchPrize__valid_line__success(): void
    {
        // Given
        $line = $this->valid_line;
        $ticket = $line->ticket;
        $prize_in_kind = $line->raffle_prize->tier->tier_prize_in_kind;
        $unique_queue_id = serialize([$ticket->whitelabel_user_id, $line->raffle_prize->raffle_rule_tier_id]);

        $lottery = $this->get_lottery();
        $lottery->is_enabled = true;

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($prize_in_kind->slug)
            ->willReturn($lottery);

        $this->prize_synchronizer
            ->expects($this->never())
            ->method('recalculatePrizes')
            ->with($line->raffle_prize);

        $this->factory
            ->expects($this->once())
            ->method('create_bonus_ticket')
            ->with(
                $ticket->whitelabel_id,
                $ticket->whitelabel_user_id,
                $prize_in_kind->slug,
                $prize_in_kind->config['count']
            );

        $this->queue_popup
            ->expects($this->once())
            ->method('once')
            ->with($unique_queue_id)
            ->willReturnSelf();

        $this->queue_popup
            ->expects($this->once())
            ->method('pushMessage')
            ->with(
                $ticket->whitelabel_id,
                $ticket->whitelabel_user_id,
                $prize_in_kind->name,
                'You have won free ticket!'
            );

        // When
        $this->service->dispatchPrize($line);
    }

    /** @test */
    public function dispatchPrize__prize_lottery_schema_has_not_count_config_key__throws_invalid_argument_exception(): void
    {
        // Except
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find config->count key in Models\RaffleRuleTierInKindPrize.');

        // Given
        $line = $this->valid_line;
        $line->raffle_prize->tier->tier_prize_in_kind->config = [];

        // When
        $this->service->dispatchPrize($line);
    }

    /** @test */
    public function dispatchPrize__lottery_is_not_enabled__throws_invalid_argument_exception(): void
    {
        $lottery = $this->get_lottery();

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('Unable to dispatch ticket prize due lottery %s is disabled.', $lottery->slug)
        );

        // Given
        $line = $this->valid_line;
        $lottery->is_enabled = false;
        $prize_in_kind = $line->raffle_prize->tier->tier_prize_in_kind;

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($prize_in_kind->slug)
            ->willReturn($lottery);

        // When
        $this->service->dispatchPrize($line);
    }

    /** @test */
    public function dispatchPrize_CustomPrizeInKindLotterySlug_Success(): void
    {
        // Given
        $line = $this->valid_line;
        $ticket = $line->ticket;
        $prize_in_kind = $line->raffle_prize->tier->tier_prize_in_kind;
        $prize_in_kind->slug = '100x-euromillions';

        $lottery = $this->get_lottery();
        $lottery->is_enabled = true;

        $expected_slug = 'euromillions';

        $this->lottery_dao
            ->expects($this->once())
            ->method('get_by_slug')
            ->with($expected_slug)
            ->willReturn($lottery);

        $this->prize_synchronizer
            ->expects($this->never())
            ->method('recalculatePrizes')
            ->with($line->raffle_prize);

        $this->factory
            ->expects($this->once())
            ->method('create_bonus_ticket')
            ->with(
                $ticket->whitelabel_id,
                $ticket->whitelabel_user_id,
                $expected_slug,
                $prize_in_kind->config['count']
            );

        // When
        $this->service->dispatchPrize($line);
    }
}
