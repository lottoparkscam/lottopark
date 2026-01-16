<?php

namespace Tests\Unit\Classes\Services;

use Models\Module;
use Models\AdminUser;
use Repositories\ModuleRepository;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\UnitOfWork\UnitOfWork;
use Stwarog\UowFuel\FuelEntityManager;
use Services\{Logs\FileLoggerService, CrmLoggerService, BrowserService, LocationService};
use Test_Unit;

class CrmLoggerTest extends Test_Unit
{
    private FuelEntityManager $entityManager;
    private CrmLoggerService $crmLoggerService;

    /** @var FileLoggerService|\PHPUnit\Framework\MockObject\MockObject  */
    private FileLoggerService $fileLoggerService;

    /** @var ModuleRepository|\PHPUnit\Framework\MockObject\MockObject  */
    private ModuleRepository $moduleRepository;

    /** @var BrowserService|\PHPUnit\Framework\MockObject\MockObject  */
    private BrowserService $browserService;

    /** @var LocationService|\PHPUnit\Framework\MockObject\MockObject  */
    private LocationService $locationService;

    private AdminUser $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->entityManager = new FuelEntityManager($this->createMock(DBConnectionInterface::class), new UnitOfWork());
        $this->moduleRepository = $this->createMock(ModuleRepository::class);
        $this->browserService = $this->createMock(BrowserService::class);
        $this->locationService = $this->createMock(LocationService::class);

        $this->crmLoggerService = new CrmLoggerService(
            $this->fileLoggerService,
            $this->entityManager,
            $this->moduleRepository,
            $this->browserService,
            $this->locationService,
        );

        $this->user = new AdminUser();
        $this->user->set([
            'id' => 1
        ]);
    }

    /** @test */
    public function log__CreateNewLog()
    {
        $message = 'Example message';
        $data = ['test' => 'abc'];
        $module = 'example_module';
        $whitelabelId = 1;

        $this->moduleRepository->expects($this->once())
            ->method('findByName')
            ->with($module)
            ->willReturn(new Module());

        $this->locationService->expects($this->once())
            ->method('getIP')
            ->willReturn('127.0.0.1');

        $this->browserService->expects($this->once())
            ->method('getBrowser')
            ->willReturn('Internet Explorer');

        $this->browserService->expects($this->once())
            ->method('getOS')
            ->willReturn('Windows 10');

        $success = $this->crmLoggerService->log(
            $this->user,
            $whitelabelId,
            $module,
            $message,
            $data
        );

        $this->assertTrue($success);
    }
}
