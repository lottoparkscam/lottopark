<?php

namespace Modules\View;

use RuntimeException;
use Webmozart\Assert\Assert;
use Wrappers\Decorators\ConfigContract;
use Wrappers\File;

use function strlen;

use const ARRAY_FILTER_USE_BOTH;
use const PATHINFO_EXTENSION;
use const PHP_URL_SCHEME;

class ViewPathResolver
{
    private ConfigContract $config;
    private File $file;

    public function __construct(ConfigContract $config, File $file)
    {
        $this->config = $config;
        $this->file = $file;
    }

    public function resolveFilePath(string $path, array $supportedExtensions): string
    {
        $dirs = $this->config->get('view.template_directories');
        $conflictStrategyFileExtension = $this->config->get('view.conflict_strategy');
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $hasExtension = !empty($extension);

        if ($hasExtension) {
            Assert::inArray($extension, $supportedExtensions);
        }

        if ($this->isPathAbsolute($path) && $hasExtension) {
            return $path;
        }

        $bareFileName = $path;

        $foundViews = [];

        foreach ($dirs as $dir) {
            if ($hasExtension) {
                $fullFileName = "{$dir}{$bareFileName}";
                if ($this->file->exists($fullFileName)) {
                    $foundViews[$dir][] = $fullFileName;
                }
                continue;
            }
            foreach ($supportedExtensions as $supportedExtension) {
                $fullFileName = "{$dir}{$bareFileName}.{$supportedExtension}";
                if ($this->file->exists($fullFileName)) {
                    $foundViews[$dir][] = $fullFileName;
                }
            }
        }

        if (count($foundViews) > 1) {
            throw new RuntimeException("Found $bareFileName in more than one location");
        }

        if (empty($foundViews)) {
            throw new RuntimeException("Attempting to find view $bareFileName, no file exists in configured locations.");
        }

        $foundViews = array_values($foundViews)[0];

        if ($hasExtension) {
            $foundViews = array_filter(
                $foundViews,
                function ($filePath) use ($extension) {
                    return pathinfo($filePath, PATHINFO_EXTENSION) === $extension;
                },
                ARRAY_FILTER_USE_BOTH
            );

            return array_values($foundViews)[0];
        }

        if (count($foundViews) > 1) {
            $foundViews = array_filter(
                $foundViews,
                function ($filePath) use ($conflictStrategyFileExtension) {
                    return pathinfo($filePath, PATHINFO_EXTENSION) === $conflictStrategyFileExtension;
                },
                ARRAY_FILTER_USE_BOTH
            );
            $foundViews = array_values($foundViews);

            if (empty($conflictStrategyFileExtension)) {
                throw new RuntimeException(
                    "Attempting to find view $bareFileName, but many files with the same name exists in " .
                    "configured locations and no conflict strategy set in config file."
                );
            }
        }

        return $foundViews[0];
    }

    /**
     * @codeCoverageIgnore
     * Copy&paste from Twig solution
     *
     * @param string $file
     * @return bool
     */
    public function isPathAbsolute(string $file): bool
    {
        return strspn($file, '/\\', 0, 1)
            || (
                strlen($file) > 3 && ctype_alpha($file[0])
                && ':' === $file[1]
                && strspn($file, '/\\', 2, 1)
            )
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}
