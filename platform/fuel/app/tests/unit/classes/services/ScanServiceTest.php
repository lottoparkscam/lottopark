<?php

namespace Tests\Unit\Classes\Services;

use Fuel\Core\CacheNotFoundException;
use GGLib\Lcs\Client\Request\GetLotteryTicketImageRequest;
use Helpers\ImageHelper;
use Helpers_Lottery;
use Models\Currency;
use Models\LcsTicket;
use Models\Lottery;
use Models\Whitelabel;
use Models\WhitelabelUserTicket;
use Models\WhitelabelUserTicketSlip;
use Ramsey\Uuid\Uuid;
use Repositories\LcsTicketRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelUserTicketRepository;
use Services\CacheService;
use Services\LcsService;
use Services\ScanService;
use Test_Unit;

class ScanServiceTest extends Test_Unit
{
    private ScanService $scanService;
    private CacheService $cacheService;
    private LcsTicketRepository $lcsTicketRepository;
    private WhitelabelUserTicket $ticket;
    private CacheNotFoundException $cacheNotFoundException;
    private WhitelabelUserTicketSlip $whitelabelUserTicketSlip;
    private LcsTicket $lcsTicket;
    private WhitelabelUserTicketRepository $whitelabelUserTicketRepository;
    private LcsService $lcsService;
    private const CORRECT_SCAN = ImageHelper::BASE_64_IMAGE . ' ';

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheService = $this->createMock(CacheService::class);
        $this->lcsTicketRepository = $this->getMockBuilder(LcsTicketRepository::class)
            ->addMethods(['findOneByWhitelabelUserTicketSlipId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->whitelabelUserTicketRepository = $this->getMockBuilder(WhitelabelUserTicketRepository::class)
            ->addMethods(['findOneById'])
            ->onlyMethods(['withRelations'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->whitelabelRepository = $this->getMockBuilder(WhitelabelRepository::class)
            ->addMethods(['findOneById'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheNotFoundException = $this->createMock(CacheNotFoundException::class);
        $this->lcsService = $this->createMock(LcsService::class);
        $this->ticket = new WhitelabelUserTicket();
        $this->ticket->currency = new Currency();
        $this->ticket->currency->code = 'USD';
        $this->whitelabelUserTicketSlip = new WhitelabelUserTicketSlip();
        $this->whitelabelUserTicketSlip->id = 1;
        $this->ticket->whitelabel_user_ticket_slip = [$this->whitelabelUserTicketSlip];
        $this->lcsTicket = new LcsTicket();
        $this->ticket->whitelabel = new Whitelabel();
        $this->ticket->lottery = new Lottery();
        $this->ticket->lottery->currency = new Currency();
        $this->ticket->whitelabel->theme = Whitelabel::LOTTOPARK_THEME;
        $this->lcsTicket->uuid = '9641d330-a02e-481d-b7a0-1ee03428baa0';
        $this->ticket->lottery->currency->code = 'PLN';
        $this->ticket->lottery->slug = 'gg-world-keno';
        $this->ticket->model = 2;
        $this->ticket->id = 1;
        $this->ticket->lottery_id = 33;
        $this->whitelabelUserTicketSlip->whitelabel_user_ticket = $this->ticket;
        $this->scanService = new ScanService($this->cacheService, $this->lcsTicketRepository, $this->lcsService, $this->whitelabelUserTicketRepository);
    }

    /** @test */
    public function getSelectedGgWorldScan_takeScanFromCache(): void
    {
        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willReturn([self::CORRECT_SCAN]);
        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertEquals([self::CORRECT_SCAN], $result);
    }

    /** @test */
    public function getSelectedGgWorldScan_getLotteryScansFromLcsEqualsNull(): void
    {
        $lotteryId = $this->ticket->lottery_id = 17;
        $this->ticket->lottery->slug = Helpers_Lottery::get_slug($lotteryId);
        $currencyCode = $this->ticket->lottery->currency->code;
        $getLotteryTicketImageRequest = new GetLotteryTicketImageRequest(
            $this->ticket->lottery->slug,
            [Uuid::fromString('9641d330-a02e-481d-b7a0-1ee03428baa0')],
            $currencyCode,
            $this->ticket->amount,
            $this->ticket->currency->code
        );

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->lcsTicketRepository
            ->expects($this->once())
            ->method('findOneByWhitelabelUserTicketSlipId')
            ->with(1)
            ->willReturn($this->lcsTicket);

        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs')
            ->with($getLotteryTicketImageRequest)
            ->willReturn('');

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain');

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertEquals([''], $result);
    }

    /**
     * @test
     * @dataProvider providerTestCases
     * @param int $inputLotteryId
     */
    public function getSelectedGgWorldScan_ticketEqualCorrectLottery(int $inputLotteryId): void
    {
        $lotteryId = $this->ticket->lottery_id = $inputLotteryId;
        $this->ticket->lottery->slug = Helpers_Lottery::get_slug($lotteryId);
        $currencyCode = $this->ticket->lottery->currency->code;
        $getLotteryTicketImageRequest = new GetLotteryTicketImageRequest(
            $this->ticket->lottery->slug,
            [Uuid::fromString('9641d330-a02e-481d-b7a0-1ee03428baa0')],
            $currencyCode,
            $this->ticket->amount,
            $this->ticket->currency->code
        );

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->lcsTicketRepository
            ->expects($this->once())
            ->method('findOneByWhitelabelUserTicketSlipId')
            ->with(1)
            ->willReturn($this->lcsTicket);

        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs')
            ->with($getLotteryTicketImageRequest)
            ->willReturn('asd');

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain');

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertTrue(ImageHelper::isImageBase64Encoded($result[0]));
    }

    public static function providerTestCases(): array
    {
        $selectedIds = [];
        foreach (ScanService::IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN as $selectedId) {
            $selectedIds[] = [$selectedId];
        }
        return $selectedIds;
    }

    /** @test */
    public function getSelectedGgWorldScan_ticketWithInCorrectLottery(): void
    {
        $this->ticket->lottery_id = 40;

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->lcsTicketRepository
            ->expects($this->never())
            ->method('findOneByWhitelabelUserTicketSlipId');

        $this->lcsService
            ->expects($this->never())
            ->method('getLotteryScansFromLcs');

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id, [], ScanService::CACHE_TIME_IN_SECONDS);

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertEquals([], $result);
    }

    /**
     * @test
     * @dataProvider providerTestModelCases
     * @param int $inputModel
     * @param array $expectedResult
     */
    public function getSelectedGgWorldScan_isNotPurchaseAndScanModel(int $inputModel, array $expectedResult): void
    {
        $this->ticket->model = $inputModel;

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->lcsTicketRepository
            ->expects($this->never())
            ->method('findOneByWhitelabelUserTicketSlipId');

        $this->lcsService
            ->expects($this->never())
            ->method('getLotteryScansFromLcs');

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id, [], ScanService::CACHE_TIME_IN_SECONDS);

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertEquals($expectedResult, $result);
    }
    public static function providerTestModelCases(): array
    {
        return [
            [1, []],
            [0, []],
            [10, []],
        ];
    }

    /** @test */
    public function getSelectedGgWorldScan_isPurchaseAndScanModel(): void
    {
        $slug = $this->ticket->lottery->slug;
        $currencyCode = $this->ticket->lottery->currency->code;
        $getLotteryTicketImageRequest = new GetLotteryTicketImageRequest(
            $this->ticket->lottery->slug,
            [Uuid::fromString('9641d330-a02e-481d-b7a0-1ee03428baa0')],
            $currencyCode,
            $this->ticket->amount,
            $this->ticket->currency->code
        );

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->lcsTicketRepository
            ->expects($this->once())
            ->method('findOneByWhitelabelUserTicketSlipId')
            ->with(1)
            ->willReturn($this->lcsTicket);

        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs')
            ->with($getLotteryTicketImageRequest)
            ->willReturn(self::CORRECT_SCAN);

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain');

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertTrue(ImageHelper::isImageBase64Encoded($result[0]));
    }

    /** @test */
    public function getSelectedGgWorldScan_ticketIsFromDoubleJack(): void
    {
        $this->ticket->whitelabel->theme = Whitelabel::DOUBLEJACK_THEME;

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('withRelations')
            ->with(['whitelabel', 'lottery'])
            ->willReturn($this->whitelabelUserTicketRepository);

        $this->whitelabelUserTicketRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($this->ticket->id)
            ->willReturn($this->ticket);

        $this->cacheService
            ->expects($this->once())
            ->method('getCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id)
            ->willThrowException($this->cacheNotFoundException);

        $this->lcsTicketRepository
            ->expects($this->never())
            ->method('findOneByWhitelabelUserTicketSlipId');

        $this->lcsService
            ->expects($this->never())
            ->method('getLotteryScansFromLcs');

        $this->cacheService
            ->expects($this->once())
            ->method('setCacheForWhitelabelByDomain')
            ->with(ScanService::CACHE_KEY . $this->ticket->id, [], ScanService::CACHE_TIME_IN_SECONDS);

        $result = $this->scanService->getSelectedGgWorldScan($this->ticket->id);
        $this->assertEquals([], $result);
    }

    /** @test */
    public function getSelectedGgWorldScan_constWithSelectedLotteryEqualsCorrectLottery(): void
    {
        $this->assertEquals(ScanService::IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN, [17, 19, 20, 33]);
    }

    /** @test */
    public function getScanForTicket_withCorrectTicket(): void
    {
        $slug = $this->ticket->lottery->slug;
        $currencyCode = $this->ticket->lottery->currency->code;
        $getLotteryTicketImageRequest = new GetLotteryTicketImageRequest(
            $this->ticket->lottery->slug,
            [Uuid::fromString('9641d330-a02e-481d-b7a0-1ee03428baa0')],
            $currencyCode,
            $this->ticket->amount,
            $this->ticket->currency->code
        );

        $this->lcsTicketRepository
            ->expects($this->once())
            ->method('findOneByWhitelabelUserTicketSlipId')
            ->with(1)
            ->willReturn($this->lcsTicket);

        $this->lcsService
            ->expects($this->once())
            ->method('getLotteryScansFromLcs')
            ->with($getLotteryTicketImageRequest);

        $this->scanService->getScansForTicket($this->ticket);
    }
}
