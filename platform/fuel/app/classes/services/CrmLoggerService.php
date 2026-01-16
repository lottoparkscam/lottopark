<?php

namespace Services;

use Carbon\Carbon;
use Exception;
use Models\Module;
use Models\AdminUser;
use Models\CrmLog;
use Repositories\ModuleRepository;
use Stwarog\UowFuel\FuelEntityManager;
use Services\Logs\FileLoggerService;

class CrmLoggerService
{
    private FileLoggerService $fileLoggerService;

    private FuelEntityManager $entityManager;

    private ModuleRepository $moduleRepository;

    private BrowserService $browserService;

    private LocationService $locationService;

    public function __construct(
        FileLoggerService $fileLoggerService,
        FuelEntityManager $entityManager,
        ModuleRepository $moduleRepository,
        BrowserService $browserService,
        LocationService $locationService,
    )
    {
        $this->fileLoggerService = $fileLoggerService;
        $this->entityManager = $entityManager;
        $this->moduleRepository = $moduleRepository;
        $this->browserService = $browserService;
        $this->locationService = $locationService;
    }

    public function log(
        AdminUser $userWhoMadeChange,
        int $whitelabelId,
        string $moduleName,
        string $message,
        array $data
    ): bool
    {
        /** @var Module $module */
        $module = $this->moduleRepository->findByName($moduleName);

        $log = new CrmLog();
        $log->set([
            'date' => Carbon::now(),
            'admin_user_id' => $userWhoMadeChange->id,
            'whitelabel_id' => $whitelabelId,
            'module_id' => $module->id,
            'message' => $message,
            'data' => json_encode($data),
            'ip' => $this->locationService->getIp(),
            'browser' => $this->browserService->getBrowser(),
            'operation_system' => $this->browserService->getOs(),
        ]);

        try {
            $this->entityManager->save($log);
            $this->entityManager->flush();
            return true;
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
            throw $exception;
            return false;
        }
    }
}