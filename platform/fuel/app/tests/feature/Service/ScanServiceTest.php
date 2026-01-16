<?php

namespace Tests\Feature\Helpers\Scan;

use Models\LcsTicket;
use Models\Whitelabel;
use Models\WhitelabelLottery;
use Models\WhitelabelUser;
use Models\WhitelabelUserTicket;
use Models\WhitelabelUserTicketSlip;
use Repositories\LcsTicketRepository;
use Repositories\LotteryRepository;
use Repositories\WhitelabelUserTicketRepository;
use Services\CacheService;
use Services\LcsService;
use Services\ScanService;
use Test_Feature;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelUserFixture;

final class ScanServiceTest extends Test_Feature
{
    private LcsService $lcsService;
    private CacheService $cacheService;
    private LcsTicketRepository $lcsTicketRepository;
    private ScanService $scanService;
    private WhitelabelUserTicketRepository $whitelabelUserTicketRepository;
    private WhitelabelFixture $whitelabelFixture;
    private LotteryRepository $lotteryRepository;
    private WhitelabelUserTicket $whitelabelUserTicket;
    private WhitelabelUserFixture $whitelabelUserFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->lcsService = $this->createMock(LcsService::class);
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->cacheService = $this->container->get(CacheService::class);
        $this->lcsTicketRepository = $this->container->get(LcsTicketRepository::class);
        $this->whitelabelUserTicketRepository = $this->container->get(WhitelabelUserTicketRepository::class);
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->scanService = new ScanService($this->cacheService, $this->lcsTicketRepository, $this->lcsService, $this->whitelabelUserTicketRepository);
        $this->whitelabelUserTicket = $this->createTicket();
    }

    private function createTicket(): WhitelabelUserTicket
    {
        $whitelabelUser = new WhitelabelUser($this->whitelabelUserFixture->with($this->whitelabelUserFixture::BASIC)->createOne()->to_array(), false);
        $whitelabel = new Whitelabel($this->whitelabelFixture->with($this->whitelabelFixture::BASIC, $this->whitelabelFixture::CURRENCY)->createOne()->to_array(), false);
        $currency = $whitelabel->currency;
        $lottery = $this->lotteryRepository->findOneBySlug('gg-world-keno');
        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->lottery_provider_id = 10;
        $whitelabelLottery->tier = 1;
        $whitelabelLottery->min_lines = 2;
        $whitelabelLottery->quick_pick_lines = 2;
        $whitelabelLottery->is_enabled = 1;
        $whitelabelLottery->model = 2;
        $whitelabelLottery->is_multidraw_enabled = 1;
        $whitelabelLottery->is_bonus_balance_in_use = 1;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 1;
        $whitelabelLottery->should_decrease_prepaid = 2;
        $whitelabelLottery->ltech_lock = 0;
        $whitelabelLottery->income = 1;
        $whitelabelLottery->incomeType = 1;
        $whitelabelLottery->volume = 1;
        $whitelabelLottery->lottery = $lottery;
        $whitelabelLottery->whitelabel = $whitelabel;
        $whitelabelLottery->save();
        $whitelabelUserTicket = new WhitelabelUserTicket();
        $whitelabelUserTicket->whitelabel_user_id = $whitelabelUser->id;
        $whitelabelUserTicket->lottery_type_id = 32;
        $whitelabelUserTicket->currency = $currency;
        $whitelabelUserTicket->valid_to_draw = '2022-06-07 20:45:00';
        $whitelabelUserTicket->draw_date = '2022-06-07 20:45:00';
        $whitelabelUserTicket->amount_local = 4;
        $whitelabelUserTicket->amount = 4;
        $whitelabelUserTicket->amount_usd = 4;
        $whitelabelUserTicket->date = '2022-06-06 20:11:00';
        $whitelabelUserTicket->payout = false;
        $whitelabelUserTicket->cost_local = 2;
        $whitelabelUserTicket->cost = 2;
        $whitelabelUserTicket->cost_usd = 2;
        $whitelabelUserTicket->income_local = 1;
        $whitelabelUserTicket->income_value = 1;
        $whitelabelUserTicket->income = 1;
        $whitelabelUserTicket->income_usd = 1;
        $whitelabelUserTicket->income_type = 1;
        $whitelabelUserTicket->is_insured = true;
        $whitelabelUserTicket->tier = 1;
        $whitelabelUserTicket->margin_local = 0;
        $whitelabelUserTicket->margin = 0;
        $whitelabelUserTicket->margin_usd = 0;
        $whitelabelUserTicket->margin_value = 0;
        $whitelabelUserTicket->bonus_amount_local = 0;
        $whitelabelUserTicket->bonus_amount_payment = 0;
        $whitelabelUserTicket->bonus_amount_usd = 0;
        $whitelabelUserTicket->bonus_amount = 0;
        $whitelabelUserTicket->bonus_amount_manager = 0;
        $whitelabelUserTicket->bonus_cost_local = 0;
        $whitelabelUserTicket->bonus_cost = 0;
        $whitelabelUserTicket->bonus_cost_usd = 0;
        $whitelabelUserTicket->bonus_cost_manager = 0;
        $whitelabelUserTicket->has_ticket_scan = false;
        $whitelabelUserTicket->ip = '172.21.0.1';
        $whitelabelUserTicket->line_count = 1;
        $whitelabelUserTicket->token = 123;
        $whitelabelUserTicket->status = 1;
        $whitelabelUserTicket->paid = 1;
        $whitelabelUserTicket->is_synchronized = 1;
        $whitelabelUserTicket->model = 2;
        $whitelabelUserTicket->whitelabel = $whitelabel;
        $whitelabelUserTicket->lottery = $lottery;
        $whitelabelUserTicket->lottery_id = 33;
        $whitelabelUserTicket->save();
        $thisTicket = $this->whitelabelUserTicketRepository->findOneById($whitelabelUserTicket->id);
        $whitelabelUserTicketSlip = new WhitelabelUserTicketSlip();
        $whitelabelUserTicketSlip->whitelabel_lottery = $whitelabelLottery;
        $whitelabelUserTicketSlip->whitelabel_user_ticket = $whitelabelUserTicket;
        $whitelabelUserTicketSlip->save();
        $thisTicket->whitelabel_user_ticket_slip = $whitelabelUserTicketSlip;
        $thisTicket->update();
        $lcsTicket = new LcsTicket();
        $lcsTicket->whitelabelUserTicketSlip = $whitelabelUserTicketSlip;
        $lcsTicket->uuid = '9641d330-a02e-481d-b7a0-1ee03428baa0';
        $lcsTicket->save();
        return $whitelabelUserTicket;
    }

    /** @test */
    public function ggWorldScanExists(): void
    {
        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs');

        $result = $this->scanService->getSelectedGgWorldScan($this->whitelabelUserTicket->id);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function ggWorldScanNotExists(): void
    {
        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs')
            ->willReturn('');

        $result = $this->scanService->getSelectedGgWorldScan($this->whitelabelUserTicket->id);
        $this->assertEmpty($result[0]);
    }
}
