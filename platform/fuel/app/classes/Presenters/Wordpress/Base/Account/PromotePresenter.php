<?php

namespace Presenters\Wordpress\Base\Account;

use Carbon\Carbon;
use Container;
use Helpers\UrlHelper;
use Helpers_Currency;
use Lotto_View;
use Models\Whitelabel;
use Models\WhitelabelAff;
use Models\WhitelabelAffCasinoGroup;
use Models\WhitelabelAffGroup;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Repositories\Aff\WhitelabelAffCommissionRepository;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\Aff\WhitelabelUserAffRepository;
use Repositories\WhitelabelAffClickRepository;
use Repositories\WhitelabelAffGroupRepository;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Services\QrCodeGeneratorService;

/**
 * Presenter for: wordpress/wp-content/themes/base/Account/PromoteView.twig
 */
final class PromotePresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;
    private WhitelabelAffClickRepository $whitelabelAffClickRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;
    private WhitelabelAffGroupRepository $whitelabelAffGroupRepository;
    private WhitelabelAffCasinoGroup $whitelabelAffCasinoGroup;
    private WhitelabelUserAffRepository $whitelabelUserAffRepository;
    private WhitelabelAffCommissionRepository $whitelabelAffCommissionRepository;
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private QrCodeGeneratorService $codeGeneratorService;
    private WhitelabelAff $affiliateUser;
    private array $user;

    // on the web, the image has a max-width of 160px, but we need 1000px for the downloaded image
    const QR_CODE_SIZE_IN_PIXELS = 1000;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
        $this->codeGeneratorService = Container::get(QrCodeGeneratorService::class);
        $this->whitelabelAffClickRepository = Container::get(WhitelabelAffClickRepository::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->whitelabelAffGroupRepository = Container::get(WhitelabelAffGroupRepository::class);
        $this->whitelabelAffCasinoGroup = Container::get(WhitelabelAffCasinoGroup::class);
        $this->whitelabelUserAffRepository = Container::get(WhitelabelUserAffRepository::class);
        $this->whitelabelAffCommissionRepository = Container::get(WhitelabelAffCommissionRepository::class);
        $this->whitelabelAffSlotCommissionRepository = Container::get(WhitelabelAffSlotCommissionRepository::class);

        $this->user = lotto_platform_user();
    }

    public function view(): string
    {
        if (empty($this->user['connected_aff_id'])) {
            return '';
        }

        $this->affiliateUser = $this->whitelabelAffRepository->findAffiliateById($this->user['connected_aff_id']);

        $lotteryReferralUrl = $this->generateLotteryReferralUrl();
        $casinoReferralUrl = $this->generateCasinoReferralUrl();
        $affiliateId = $this->affiliateUser->id;
        $whitelabelId = $this->whitelabel->id;
        $userTimezone = $this->user['timezone'] ?: null;

        $currentDate = Carbon::now()->setTimezone($userTimezone);
        $currentDateFormat = $currentDate->format('Y-m-d H:i:s');
        $firstDateOfThisMonth = $currentDate->startOfMonth()->format('Y-m-d H:i:s');
        $lastDateOfThisMonth = $currentDate->endOfMonth()->format('Y-m-d H:i:s');
        $date30DaysAgo = Carbon::now()->setTimezone($userTimezone)->subDays(30)->format('Y-m-d H:i:s');
        $date60DaysAgo = Carbon::now()->setTimezone($userTimezone)->subDays(60)->format('Y-m-d H:i:s');

        return $this->forge([
            'whitelabelTheme' => $this->whitelabel->theme,
            'whitelabelName' => $this->whitelabel->name,
            'lotteryReferralUrl' => $lotteryReferralUrl,
            'casinoReferralUrl' => $casinoReferralUrl,
            'lotteryQrCodeInBase64' => $this->codeGeneratorService->generate($lotteryReferralUrl, self::QR_CODE_SIZE_IN_PIXELS),
            'casinoQrCodeInBase64' => $this->codeGeneratorService->generate($casinoReferralUrl, self::QR_CODE_SIZE_IN_PIXELS),
            'totalCommission' => $this->getAffiliateTotalCommission(),
            'totalCommissionThisMonth' => $this->getAffiliateTotalCommission($firstDateOfThisMonth, $lastDateOfThisMonth),
            'totalCommissionLast30Days' => $this->getAffiliateTotalCommission($date30DaysAgo, $currentDateFormat),
            'totalCommissionLast60Days' => $this->getAffiliateTotalCommission($date60DaysAgo, $currentDateFormat),
            'clickCount' => $this->whitelabelAffClickRepository->getClickCountByAffiliateId($affiliateId),
            'clickCountLast30Days' => $this->whitelabelAffClickRepository->getClickCountByAffiliateId($affiliateId, $date30DaysAgo, $currentDateFormat),
            'clickCountLast60Days' => $this->whitelabelAffClickRepository->getClickCountByAffiliateId($affiliateId, $date60DaysAgo, $currentDateFormat),
            'registerCount' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId),
            'registerCountLast30Days' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId, $date30DaysAgo, $currentDateFormat),
            'registerCountLast60Days' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId, $date60DaysAgo, $currentDateFormat),
            'activeUsersCount' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId, null, null, true),
            'activeUsersCountLast30Days' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId, $date30DaysAgo, $currentDateFormat, true),
            'activeUsersCountLast60Days' => $this->whitelabelUserAffRepository->getUsersCountByAffiliateId($affiliateId, $whitelabelId, $date60DaysAgo, $currentDateFormat, true),
            'groupData' => $this->prepareAffiliateGroupDataForView(),
            'promoteChartData' => $this->getAffiliateChartData($date30DaysAgo, $currentDateFormat), // TODO: lottery, casino
        ]);
    }

    private function generateLotteryReferralUrl(): string
    {
        return 'https://' . $this->whitelabel->domain . '/?ref=' . strtoupper($this->affiliateUser->token);
    }

    private function generateCasinoReferralUrl(): string
    {
        return UrlHelper::changeAbsoluteUrlToCasinoUrl(
            $this->generateLotteryReferralUrl(),
            true
        );
    }

    private function isAffiliateCommissionGroupSet(): bool
    {
        return !empty($this->affiliateUser['whitelabel_aff_group_id']);
    }

    private function prepareAffiliateGroupDataForView(): array
    {
        $groupData = [];
        if ($this->isAffiliateCommissionGroupSet()) {
            $affiliateLotteryGroupData = $this->getAffiliateLotteryGroupData($this->affiliateUser);

            $groupData['firstTierLotterySaleCommission'] = round($affiliateLotteryGroupData->commissionValueManager);
            $groupData['secondTierLotterySaleCommission'] = round($affiliateLotteryGroupData->commissionValue_2Manager);
        } else {
            $groupData['firstTierLotterySaleCommission'] = round($this->whitelabel->defCommissionValueManager);
            $groupData['secondTierLotterySaleCommission'] = round($this->whitelabel->def_commission_value_2_manager);
        }

        return $groupData;
    }

    private function getAffiliateCommissionData(?string $dateStart, ?string $dateEnd): array
    {
        $affiliateId = $this->affiliateUser->id;
        $whitelabelId = $this->whitelabel->id;

        $lotteryCommissions = $this->whitelabelAffCommissionRepository->findCommissions(
            [],
            ['campaign' => 'LEFT', 'medium' => 'LEFT', 'content' => 'LEFT'],
            null,
            null,
            $affiliateId,
            false,
            $dateStart,
            $dateEnd,
            $whitelabelId,
        );

        $lotteryCommissionsTierSecond = $this->whitelabelAffCommissionRepository->findCommissions(
            [],
            ['campaign' => 'LEFT', 'medium' => 'LEFT', 'content' => 'LEFT'],
            null,
            $affiliateId,
            null,
            true,
            $dateStart,
            $dateEnd,
            $whitelabelId,
        );

        $casinoCommissions = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissions(
            [],
            ['campaign' => 'LEFT', 'medium' => 'LEFT', 'content' => 'LEFT'],
            null,
            null,
            $affiliateId,
            false,
            $dateStart,
            $dateEnd,
            $whitelabelId,
        );

        $casinoCommissionsTierSecond = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissions(
            [],
            ['campaign' => 'LEFT', 'medium' => 'LEFT', 'content' => 'LEFT'],
            null,
            $affiliateId,
            null,
            true,
            $dateStart,
            $dateEnd,
            $whitelabelId,
        );

        return [
            array_merge($lotteryCommissions->as_array(), $lotteryCommissionsTierSecond->as_array()),
            array_merge($casinoCommissions->as_array(), $casinoCommissionsTierSecond->as_array()),
        ];
    }

    private function getAffiliateTotalCommission(?string $dateStart = null, ?string $dateEnd = null): string
    {
        [$lotteryCommissions, $casinoCommissions] = $this->getAffiliateCommissionData($dateStart, $dateEnd);

        $totalLotteryCommission = $this->calculateTotalCommission($lotteryCommissions, 'commission_manager');
        $totalCasinoCommission = $this->calculateTotalCommission($casinoCommissions, 'daily_commission_usd');

        $totalCommission = round($totalLotteryCommission + $totalCasinoCommission, 2);

        if (isset($lotteryCommissions[0]['manager_currency_code'])) {
            return $this->formatAmountToManagerCurrency($totalCommission, $lotteryCommissions[0]['manager_currency_code']);
        } else {
            return $this->formatAmountToManagerCurrency($totalCommission, $this->whitelabel->currency->code ?? 'USD');
        }
    }

    private function getAffiliateChartData(?string $dateStart, ?string $dateEnd): array
    {
        [$lotteryCommissions, $casinoCommissions] = $this->getAffiliateCommissionData($dateStart, $dateEnd);

        return $this->prepareDataForLineChart($lotteryCommissions, $casinoCommissions);
    }

    private function getCombinedDates(array $lotteryCommissions, array $casinoCommissions): array
    {
        $lotteryDates = array_column($lotteryCommissions, 'date_confirmed');
        $casinoDates = array_column($casinoCommissions, 'created_at');

        $combinedDates = array_unique(array_merge($lotteryDates, $casinoDates));
        sort($combinedDates);

        return $combinedDates;
    }

    private function prepareDataForLineChart(array $lotteryCommissions, array $casinoCommissions): array
    {
        $combinedDates = $this->getCombinedDates($lotteryCommissions, $casinoCommissions);

        $lotteryData = $this->prepareChartData($lotteryCommissions, 'commission_manager', _('Lottery Commissions'), $combinedDates);
        $casinoData = $this->prepareChartData($casinoCommissions, 'daily_commission_usd', _('Casino Commissions'), $combinedDates);

        return [$lotteryData, $casinoData];
    }

    private function prepareChartData(array $commissions, string $commissionKey, string $seriesName, array $combinedDates): array
    {
        $data = [
            'name' => $seriesName,
            'data' => [],
            'categories' => []
        ];

        $dateCommissionData = array_fill_keys($combinedDates, 0);

        foreach ($commissions as $commission) {
            $dateConfirmed = $commission['date_confirmed'] ?? $commission['created_at'];
            $commissionValue = $commission[$commissionKey] ?? 0;
            $dateCommissionData[$dateConfirmed] += $commissionValue;
        }

        foreach ($dateCommissionData as $date => $value) {
            $formattedDate = date('d-m', strtotime($date));
            $data['categories'][] = $formattedDate;
            $data['data'][] = $value;
        }

        return $data;
    }

    private function calculateTotalCommission(array $commissions, string $commissionKey): float
    {
        $totalCommission = 0;

        foreach ($commissions as $commission) {
            $totalCommission = round($totalCommission + $commission[$commissionKey], 2);
        }

        return $totalCommission;
    }

    private function getAffiliateLotteryGroupData(WhitelabelAff $affiliateUser): WhitelabelAffGroup
    {
        return $this->whitelabelAffGroupRepository->findOneById($affiliateUser->whitelabelAffGroupId);
    }

    private function getAffiliateCasinoGroupData(WhitelabelAff $affiliateUser): WhitelabelAffCasinoGroup
    {
        return $this->whitelabelAffCasinoGroup->findOneById($affiliateUser->whitelabelAffCasinoGroupId);
    }

    private function formatAmountToManagerCurrency(float $amount, string $managerCurrencyCode): string
    {
        $amountWithCurrencySymbol = Lotto_View::format_currency(
            $amount,
            $managerCurrencyCode,
            true
        );

        preg_match('/([^\d]*)(\d+\.\d+)/', $amountWithCurrencySymbol, $matches);

        if (count($matches) === 3) {
            $currencySymbol = $matches[1];
            $numericValue = $matches[2];

            return "<span class=\"sup\">$currencySymbol</span> $numericValue";
        }

        return $amountWithCurrencySymbol;
    }
}
