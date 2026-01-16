<?php

namespace Services\Plugin;

use Models\WhitelabelUser;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Logs\FileLoggerService;

abstract class PluginService
{
    private ?WhitelabelUser $whitelabelUser = null;

    protected WhitelabelUserRepository $whitelabelUserRepository;
    protected FileLoggerService $fileLoggerService;

    public function __construct(
        WhitelabelUserRepository $whitelabelUserRepository,
        FileLoggerService $fileLoggerService,
    ) {
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function setWhitelabelUser(int $userId): void
    {
        $this->whitelabelUser = $this->whitelabelUserRepository->findOneById($userId);
    }

    public function getWhitelabelUser(): ?WhitelabelUser
    {
        return $this->whitelabelUser;
    }
}