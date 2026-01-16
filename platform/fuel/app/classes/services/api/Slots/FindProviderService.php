<?php

namespace Services\Api\Slots;

use Helpers\CaseHelper;
use Helpers\UrlHelper;
use Repositories\SlotProviderRepository;

class FindProviderService
{
    private SlotProviderRepository $slotProviderRepository;
    private string $providerSlug;

    /**
     * @param int $slotProviderId base on it finds correct provider name to initialiseClass()
     */
    public function __construct(
        int $slotProviderId,
        SlotProviderRepository $slotProviderRepository
    ) {
        $this->slotProviderRepository = $slotProviderRepository;
        $this->providerSlug = $this->slotProviderRepository->findSlotProviderSlugById($slotProviderId);
    }

    /**
     * This function generate class name in order to simplify adding new providers.
     * When class doesn't exist (it is fatal error) redirects user to homepage
     * It should happen only on dev during adding new provider.
     * Now it's only init but I have made it with switch because there may appear
     * more specific classes per provider
     * example class name for init: {$nameSpace}\{$providerName}InitGameService
     */
    public function initClass(string $classType): string
    {
        $providerSlug = CaseHelper::kebabToPascal($this->providerSlug);
        $className = '';
        switch ($classType) {
            case $classType === 'init':
                $className = $providerSlug . 'InitGameService';
                break;
        }

        if (class_exists("\\" . __NAMESPACE__ . "\\" . $className)) {
            return __NAMESPACE__ . "\\" . $className;
        }

        UrlHelper::redirectToHomepage();
        return '';
    }
}
