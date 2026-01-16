<?php

namespace Core;

use Wrappers\Decorators\ConfigContract;

/** This class was created in order to avoid editing real fuel core. */
class App
{
    private ConfigContract $configContract;

    public const DEVELOPMENT = 'development';
    public const STAGING = 'staging';
    public const PRODUCTION = 'production';
    public const PRE_MASTER = 'pre-master';
    public const TEXTS = 'texts';
    public const REVIEW_APP = 'review-app';
    public const TEST = 'test';

    public function __construct(ConfigContract $configContract)
    {
        $this->configContract = $configContract;
    }

    public function getServerType(): string
    {
        return $this->configContract->get('App.serverType');
    }

    public function isDevelopment(): bool
    {
        return $this->configContract->get('App.serverType') === self::DEVELOPMENT;
    }

    public function isPreMaster(): bool
    {
        return $this->configContract->get('App.serverType') === self::PRE_MASTER;
    }

    public function isProduction(): bool
    {
        return $this->configContract->get('App.serverType') === self::PRODUCTION;
    }

    public function isNotProduction(): bool
    {
        return !$this->isProduction();
    }

    public function isReviewApp(): bool
    {
        return $this->configContract->get('App.serverType') === self::REVIEW_APP;
    }

    public function isNotReviewApp(): bool
    {
        return !$this->isReviewApp();
    }

    public function isTexts(): bool
    {
        return $this->configContract->get('App.serverType') === self::TEXTS;
    }

    public function isTest(): bool
    {
        return $this->configContract->get('App.serverType') === self::TEST;
    }

    public function isStaging(): bool
    {
        return $this->configContract->get('App.serverType') === self::STAGING;
    }
}
