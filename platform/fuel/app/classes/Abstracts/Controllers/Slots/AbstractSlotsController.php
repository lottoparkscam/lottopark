<?php

namespace Abstracts\Controllers\Slots;

use Abstracts\Controllers\Internal\AbstractPublicController;
use Container;
use Fuel\Core\Input;
use Models\Whitelabel;
use Models\WhitelabelSlotProvider;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Services\Api\Slots\LoggerService;
use Services\Api\Slots\SecurityService;
use Services\Logs\FileLoggerService;
use Throwable;

abstract class AbstractSlotsController extends AbstractPublicController
{
    protected const PROVIDER_SLUG = '';

    protected FileLoggerService $fileLoggerService;
    protected LoggerService $logger;
    protected Whitelabel $whitelabelFromUrl;
    protected SecurityService $securityService;
    protected WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    protected WhitelabelSlotProvider $whitelabelSlotProvider;
    private WhitelabelRepository $whitelabelRepository;
    protected array $requestData;
    protected array $errors = [];

    public function before()
    {
        parent::before();
        $this->logger = Container::get(LoggerService::class);
        $this->securityService = Container::get(SecurityService::class);
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    protected function checkRequest(): bool
    {
        try {
            $this->whitelabelFromUrl = $this->whitelabelRepository->getWhitelabelFromUrl();
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );

            $this->errors[] = 'We cannot find that whitelabel';
            return false;
        }

        $requestData = filter_var_array(Input::post(), FILTER_SANITIZE_STRING);
        if (empty($requestData)) {
            $this->errors[] = 'No POST data provided';
            return false;
        }

        $this->requestData = $requestData;

        $providerSlug = static::PROVIDER_SLUG;
        $whitelabelSlotProvider = $this->whitelabelSlotProviderRepository->findByWhitelabelAndProviderSlug(
            $this->whitelabelFromUrl->id,
            $providerSlug
        );

        if (empty($whitelabelSlotProvider)) {
            $this->errors[] = "This whitelabel does not have {$providerSlug} integration";
            return false;
        }

        $this->whitelabelSlotProvider = $whitelabelSlotProvider;

        $hasNotAllowedIp = !$this->securityService->isCurrentIpAllowed(
            $this->whitelabelSlotProvider->slotProviderId
        );

        if ($hasNotAllowedIp) {
            $this->errors[] = "This IP is not allowed";
            return false;
        }

        return true;
    }

    protected function getFirstErrorMessage(): string
    {
        return $this->errors[0];
    }

    protected function checkRequiredFieldsInRequest(array $requiredFields): bool
    {
        return $this->checkRequiredFields($requiredFields, $this->requestData);
    }

    private function checkRequiredFields(array $requiredKeys, array $context = []): bool
    {
        $errorMessage = "key does not exist in request data or value is empty";
        foreach ($requiredKeys as $nestedKeysName => $keyName) {
            if (is_array($keyName)) {
                $nestedKeys = $keyName;
                $keyName = $nestedKeysName;
            }

            /** additional !isset to accept 0 values */
            $keyNotExists = !isset($context[$keyName]) && empty($context[$keyName]);
            if ($keyNotExists) {
                $upperKey = ucfirst($keyName);
                $this->errors[] = "$upperKey $errorMessage";
                return false;
            }

            if (!empty($nestedKeys) && is_array($context[$keyName])) {
                foreach ($context[$keyName] as $item) {
                    $nestedKeysAreNotCorrect = !$this->checkRequiredFields($nestedKeys, $item);
                    if ($nestedKeysAreNotCorrect) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
