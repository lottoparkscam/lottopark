<?php

namespace Repositories;

use Exception;
use Helpers\FullTokenHelper;
use Services\CacheService;
use Lotto_Helper;
use Models\Whitelabel;
use Repositories\Orm\AbstractRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Services\Logs\FileLoggerService;

/**
 * @method Whitelabel|null findOneByTheme(string $whitelabelTheme)
 * @method Whitelabel|null findOneById(int $whitelabelId)
 * @method Whitelabel|null findByIsActive(bool $true)
 * @method Whitelabel|null findOneByPrefix(string $prefix)
 */
class WhitelabelRepository extends AbstractRepository
{
    private CacheService $cacheService;
    private FileLoggerService $fileLoggerService;

    public function __construct(
        Whitelabel $model,
        CacheService $cacheService,
        FileLoggerService $fileLoggerService
    ) {
        parent::__construct($model);
        $this->cacheService = $cacheService;
        $this->fileLoggerService = $fileLoggerService;
    }

    /** This function overrides method from trait */
    public function findOneByDomain(string $domain): ?Whitelabel
    {
        $cacheKey = 'whitelabel';
        $isWhitelabelV1 = in_array($domain, $this->getAllWhitelabelV1Domains());

        $whitelabelCriteria = $this->pushCriterias([
            new Model_Orm_Criteria_Where('domain', $domain)
        ]);

        /** We don't use cache on v2 whitelabels because we shouldn't cache `prepaid` column  */
        if ($isWhitelabelV1) {
            return $this->cacheService->getAndSaveQueryForWhitelabelByDomain(
                $cacheKey,
                $whitelabelCriteria,
                $this->cacheService::FIND_ONE,
                $this->cacheService::TEN_MINUTES_IN_SECONDS,
                []
            );
        }

        return $whitelabelCriteria->findOne();
    }

    private function getAllWhitelabelDomainsByType(int $type): array
    {
        $cacheKey = 'whitelabel_v' . $type . '_domains';
        $whitelabelDomainsBasedOnProvidedType = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['domain']),
            new Model_Orm_Criteria_Where('type', $type)
        ]);

        $result = $this->cacheService->getAndSaveQueryGlobal(
            $cacheKey,
            $whitelabelDomainsBasedOnProvidedType,
            $this->cacheService::GET_RESULTS_FOR_SINGLE_FIELD,
            $this->cacheService::TEN_MINUTES_IN_SECONDS,
            []
        );

        $this->model::flush_cache();

        return $result;
    }

    public function getAllWhitelabelDomains(): array
    {
        $cacheKey = 'whitelabels_domains';
        $whitelabelsDomains = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['domain'])
        ]);

        $result = $this->cacheService->getAndSaveQueryGlobal(
            $cacheKey,
            $whitelabelsDomains,
            $this->cacheService::GET_RESULTS_FOR_SINGLE_FIELD,
            $this->cacheService::TEN_MINUTES_IN_SECONDS,
            []
        );

        $this->model::flush_cache();

        return $result;
    }

    public function getAllActiveWhitelabelDomains(): array
    {
        $cacheKey = 'active_whitelabels_domains';
        $whitelabelsDomains = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['domain']),
            new Model_Orm_Criteria_Where('is_active', true),
        ]);

        $result = $this->cacheService->getAndSaveQueryGlobal(
            $cacheKey,
            $whitelabelsDomains,
            $this->cacheService::GET_RESULTS_FOR_SINGLE_FIELD,
            $this->cacheService::TEN_MINUTES_IN_SECONDS,
            []
        );

        $this->model::flush_cache();

        return $result;
    }

    public function getAllActiveWhitelabels(): array
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id', 'language_id', 'theme', 'is_active']),
            new Model_Orm_Criteria_Where('is_active', true),
        ])->getResults() ?? [];
    }

    public function getAllNotActiveWhitelabels(): array
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id', 'language_id', 'theme', 'is_active']),
            new Model_Orm_Criteria_Where('is_active', false),
        ])->getResults() ?? [];
    }

    public function getAllWhitelabelV2Domains(): array
    {
        return $this->getAllWhitelabelDomainsByType(Whitelabel::TYPE_V2);
    }

    public function getAllWhitelabelV1Domains(): array
    {
        return $this->getAllWhitelabelDomainsByType(Whitelabel::TYPE_V1);
    }

    /** @throws Exception */
    public function getWhitelabelFromUrl(): Whitelabel
    {
        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $whitelabel = $this->findOneByDomain($domain);

        if (empty($whitelabel)) {
            throw new Exception("Whitelabel does not exists for domain {$domain}");
        }

        return $whitelabel;
    }

    public function countV2(): int
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where('type', Whitelabel::TYPE_V2));
        return $this->getCount();
    }

    public function countWithEnabledSlots(): int
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_slot_providers'),
            new Model_Orm_Criteria_Where('whitelabel_slot_providers.id', null, 'IS NOT')
        ]);

        return $this->getCount();
    }

    public function getThemeById(int $whitelabelId): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['theme']),
            new Model_Orm_Criteria_Where('id', $whitelabelId)
        ])->findOne();

        $this->model::flush_cache();

        return $whitelabel->theme ?? '';
    }

    public function getCompanyDetailsByTheme(string $theme): string
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['company_details']),
            new Model_Orm_Criteria_Where('theme', $theme)
        ])->findOne();

        $this->model::flush_cache();

        return $whitelabel->companyDetails;
    }

    public function updateContactEmails(Whitelabel $whitelabel, string $supportEmail, string $paymentEmail): bool
    {
        try {
            $whitelabel->supportEmail = $supportEmail;
            $whitelabel->paymentEmail = $paymentEmail;
            $whitelabel->save();
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                "Couldn't update contact email for whitelabel: {$exception->getMessage()}"
            );
            return false;
        }
        return true;
    }

    public function updateSupportEmail(Whitelabel $whitelabel, string $supportEmail): bool
    {
        try {
            $whitelabel->supportEmail = $supportEmail;
            $whitelabel->save();
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                "Couldn't update contact email for whitelabel: {$exception->getMessage()}"
            );
            return false;
        }
        return true;
    }

    public function updatePaymentEmail(Whitelabel $whitelabel, string $paymentEmail): bool
    {
        try {
            $whitelabel->paymentEmail = $paymentEmail;
            $whitelabel->save();
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                "Couldn't update contact email for whitelabel: {$exception->getMessage()}"
            );
            return false;
        }
        return true;
    }

    public function updateDefaultCasinoCommissionValues(
        int $whitelabelId,
        float $commissionPercentageValueForTier1,
        float $commissionPercentageValueForTier2
    ): void {
        $this->db->update(Whitelabel::get_table_name())
            ->set([
                'default_casino_commission_percentage_value_for_tier_1' => $commissionPercentageValueForTier1,
                'default_casino_commission_percentage_value_for_tier_2' => $commissionPercentageValueForTier2
            ])
            ->where('id', $whitelabelId)
            ->execute();
    }

    /**
     * Full token contains whitelabel prefix like LP + type transaction(T)/deposit(D)/purchase(P)/user(U) + whitelabel_user.token or other
     * For example -> LPP12334567
     */
    public function getWhitelabelByFullToken(string $fullToken): Whitelabel
    {
        $whitelabelPrefix = FullTokenHelper::getWhitelabelPrefix($fullToken);
        return $this->findOneByPrefix($whitelabelPrefix);
    }
}
