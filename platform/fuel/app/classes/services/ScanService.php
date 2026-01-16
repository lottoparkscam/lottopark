<?php

namespace Services;

use Fuel\Core\CacheNotFoundException;
use GGLib\Lcs\Client\Request\GetLotteryTicketImageRequest;
use Helpers\ImageHelper;
use Helpers_Lottery;
use Models\WhitelabelUserTicket;
use Models\WhitelabelUserTicketSlip;
use Ramsey\Uuid\Uuid;
use Repositories\LcsTicketRepository;
use Repositories\WhitelabelUserTicketRepository;

class ScanService
{
    private CacheService $cacheService;
    private LcsTicketRepository $lcsTicketRepository;
    private LcsService $lcsService;
    private WhitelabelUserTicketRepository $whitelabelUserTicketRepository;
    public const CACHE_TIME_IN_SECONDS = 5;
    public const CACHE_KEY = 'ggWorldScanImages';
    public const IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN =  [Helpers_Lottery::GGWORLD_ID, Helpers_Lottery::GGWORLD_X_ID, Helpers_Lottery::GGWORLD_MILLION_ID, Helpers_Lottery::KENO_ID];

    public function __construct(CacheService $cacheService, LcsTicketRepository $lcsTicketRepository, LcsService $lcsService, WhitelabelUserTicketRepository $whitelabelUserTicketRepository)
    {
        $this->cacheService = $cacheService;
        $this->lcsService = $lcsService;
        $this->lcsTicketRepository = $lcsTicketRepository;
        $this->whitelabelUserTicketRepository = $whitelabelUserTicketRepository;
    }

    /**
     * When the ticket is too big, we divide the ticket into several slips.
     * One Slip is assigned with unique uuid in LcsTicket, and we download scans from lcs for this unique id.
     */
    public function getScansForTicket(WhitelabelUserTicket $ticket): array
    {
        $images = [];
        $whitelabelUserTicketSlips = $ticket->whitelabel_user_ticket_slip;
        $lotterySlug = $ticket->lottery->slug;
        $currencyCode = $ticket->lottery->currency->code;
        foreach ($whitelabelUserTicketSlips as $whitelabelUserTicketSlip) {
            $images[] = self::getScanForWhitelabelUserTicketSlip($whitelabelUserTicketSlip, $lotterySlug, $currencyCode);
        }
        return $images;
    }

    public function getSelectedGgWorldScan(int $ticketId): array
    {
        try {
            $images = $this->cacheService->getCacheForWhitelabelByDomain(self::CACHE_KEY . $ticketId);
        } catch (CacheNotFoundException $exception) {
            $images = [];
            $ticket = $this->whitelabelUserTicketRepository->withRelations(['whitelabel', 'lottery'])->findOneById($ticketId);
            $ticketEqualsCorrectLottery = $ticket->isPurchaseAndScanModel() && in_array($ticket->lottery_id, ScanService::IDS_OF_GG_WORLD_LOTTERIES_WITH_ENABLED_SCAN) && $ticket->isNotTicketFromDoubleJack();
            if ($ticketEqualsCorrectLottery) {
                $images = self::getScansForTicket($ticket);
            }
            $this->cacheService->setCacheForWhitelabelByDomain(self::CACHE_KEY . $ticketId, $images, self::CACHE_TIME_IN_SECONDS);
        }

        return $images;
    }

    private function getScanForWhitelabelUserTicketSlip(WhitelabelUserTicketSlip $whitelabelUserTicketSlip, string $lotterySlug, string $currencyCode): string
    {
        $image = '';
        $lcsTicket = $this->lcsTicketRepository->findOneByWhitelabelUserTicketSlipId($whitelabelUserTicketSlip->id);
        if (!empty($lcsTicket)) {
            $uuids[] = Uuid::fromString($lcsTicket->uuid);
            $lotteryTicketImageRequest = new GetLotteryTicketImageRequest(
                $lotterySlug,
                $uuids,
                $currencyCode,
                $whitelabelUserTicketSlip->whitelabel_user_ticket->amount,
                $whitelabelUserTicketSlip->whitelabel_user_ticket->currency->code,
            );
            $lotteryScansFromLcs = $this->lcsService->getLotteryScansFromLcs($lotteryTicketImageRequest);
            $image = empty($lotteryScansFromLcs) ? '' : ImageHelper::generateBase64Image($lotteryScansFromLcs);
        }
        return $image;
    }
}
