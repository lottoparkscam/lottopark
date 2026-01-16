<?php

namespace Services\Api\Balance;

use Throwable;
use Wrappers\Decorators\ConfigContract;

class DocService
{
    private ConfigContract $config;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    public function grant_cors_access(): void
    {
        $crmUrl = substr_replace($this->config->get('translations.wp.url'), 'admin.', 8, 0);
        header("Access-Control-Allow-Origin: $crmUrl");
    }

    public function get_doc_in_json(): ?string
    {
        $filePath = $this->config->get('cache.api_doc.path') . 'doc.json';
        try {
            $fileContent = file_get_contents($filePath);
        } catch (Throwable $throwable) {
            return null;
        }

        return $fileContent;
    }
}