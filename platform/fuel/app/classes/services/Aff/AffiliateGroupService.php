<?php

namespace Services;

use Fuel\Core\Input;
use Helpers\SanitizerHelper;
use Repositories\WhitelabelAffCasinoGroupRepository;
use Repositories\WhitelabelRepository;
use Validators\AffiliateCasinoGroupCreateFormValidator;
use Validators\AffiliateCasinoGroupEditFormValidator;

class AffiliateGroupService
{
    public const DEFAULT_CASINO_AFF_GROUP_ID = 0;

    /** @var array<string, string> */
    public const INPUT_NAMES = [
        'groupName' => 'input.groupName',
        'commissionValueTier1' => 'input.commissionPercentageValueForTier1',
        'commissionValueTier2' => 'input.commissionPercentageValueForTier2',
    ];

    protected array $errors = [];

    private AffiliateCasinoGroupEditFormValidator $affiliateCasinoGroupEditFormValidator;
    private AffiliateCasinoGroupCreateFormValidator $affiliateCasinoGroupCreateFormValidator;
    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;
    private WhitelabelRepository $whitelabelRepository;

    public function __construct(
        AffiliateCasinoGroupEditFormValidator $affiliateCasinoGroupEditFormValidator,
        AffiliateCasinoGroupCreateFormValidator $affiliateCasinoGroupCreateFormValidator,
        WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository,
        WhitelabelRepository $whitelabelRepository,
    ) {
        $this->affiliateCasinoGroupEditFormValidator = $affiliateCasinoGroupEditFormValidator;
        $this->affiliateCasinoGroupCreateFormValidator = $affiliateCasinoGroupCreateFormValidator;
        $this->whitelabelAffCasinoGroupRepository = $whitelabelAffCasinoGroupRepository;
        $this->whitelabelRepository = $whitelabelRepository;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function updateCommissionValuesForGroup(int $groupId, int $whitelabelId): bool
    {
        if (!$this->validateEditForm()) {
            return false;
        }

        $commissionPercentageValueForTier1 = (float)Input::post(self::INPUT_NAMES['commissionValueTier1']);
        $commissionPercentageValueForTier2 = (float)Input::post(self::INPUT_NAMES['commissionValueTier2']);

        $isDefaultGroup = $this->isDefaultGroup($groupId);

        if ($isDefaultGroup) {
            $this->whitelabelRepository->updateDefaultCasinoCommissionValues(
                $whitelabelId,
                $commissionPercentageValueForTier1,
                $commissionPercentageValueForTier2
            );

            return true;
        }

        $this->whitelabelAffCasinoGroupRepository->updateCommissionValuesForGroup(
            (int)$groupId,
            $whitelabelId,
            $commissionPercentageValueForTier1,
            $commissionPercentageValueForTier2
        );

        return true;
    }

    public function createCommissionGroup(int $whitelabelId): bool
    {
        if (!$this->validateCreateForm()) {
            return false;
        }

        $groupName = SanitizerHelper::sanitizeString(Input::post(self::INPUT_NAMES['groupName']));
        $commissionPercentageValueForTier1 = (float)Input::post(self::INPUT_NAMES['commissionValueTier1']);
        $commissionPercentageValueForTier2 = (float)Input::post(self::INPUT_NAMES['commissionValueTier2']);

        $this->whitelabelAffCasinoGroupRepository->create(
            $whitelabelId,
            $groupName,
            $commissionPercentageValueForTier1,
            $commissionPercentageValueForTier2
        );

        return true;
    }

    private function validateEditForm(): bool
    {
        $isRequestInvalid = !$this->affiliateCasinoGroupEditFormValidator->isValid();
        if ($isRequestInvalid) {
            $this->errors = $this->affiliateCasinoGroupEditFormValidator->getErrors();
            return false;
        }

        return true;
    }

    private function validateCreateForm(): bool
    {
        $isRequestInvalid = !$this->affiliateCasinoGroupCreateFormValidator->isValid();
        if ($isRequestInvalid) {
            $this->errors = $this->affiliateCasinoGroupCreateFormValidator->getErrors();
            return false;
        }

        return true;
    }

    private function isDefaultGroup(int $groupId): bool
    {
        return $groupId === self::DEFAULT_CASINO_AFF_GROUP_ID;
    }
}
