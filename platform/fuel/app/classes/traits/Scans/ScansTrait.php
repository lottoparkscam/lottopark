<?php

namespace Traits\Scans;

use Container;
use Exception;
use Services\Logs\FileLoggerService;
use Services\ScanService;

trait ScansTrait
{
    public function getGgWorldScanImages(array $existingImages, int $ticketId): array
    {
        /** When images in view not exists we fetch to lcs for new */
        if (empty($existingImages)) {
            try {
                /** @var ScanService $scanService */
                $scanService = Container::get(ScanService::class);
                $existingImages = $scanService->getSelectedGgWorldScan($ticketId);
                if (empty($existingImages)) {
                    return [];
                }
            } catch (Exception $exception) {
                $fileLoggerService = Container::get(FileLoggerService::class);
                $fileLoggerService->error('Could not download scan. Message: ' . $exception->getMessage());
            }
        }
        return $existingImages;
    }
}
