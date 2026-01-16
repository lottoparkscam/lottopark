<?php

namespace Services\Files;

use Exceptions\Files\FileNotFoundException;
use Fuel\Core\File;
use Fuel\Core\InvalidPathException;
use Fuel\Core\FileAccessException;
use Fuel\Core\OutsideAreaException;
use SplFileObject;
use LimitIterator;

class FileService
{
    /** @throws FileNotFoundException */
    public function exists(string $pathToFile): bool
    {
        $fileNotExists = !file_exists($pathToFile);

        if ($fileNotExists) {
            throw new FileNotFoundException($pathToFile);
        }

        return true;
    }

    /**
     * @throws InvalidPathException
     * @throws FileAccessException
     * @throws OutsideAreaException
     */
    public function update(string $pathToFile, string $content): bool
    {
        $details = pathinfo($pathToFile);
        $fileName = $details['basename'];
        $basePath = $details['dirname'];

        return File::update(
            $basePath,
            $fileName,
            $content
        );
    }

    /**
     * @throws InvalidPathException
     * @throws FileAccessException
     * @throws OutsideAreaException
     */
    public function pushToLine(string $pathToFile, string $content, int $lineNumber): bool
    {
        $currentContent = File::read($pathToFile, true);
        $currentContentArray = explode("\n", $currentContent);
        $countOfLines = count($currentContentArray);

        if ($lineNumber > $countOfLines) {
            return false;
        }

        array_splice($currentContentArray, $lineNumber, 0, $content);
        $updatedContent = implode("\n", $currentContentArray);

        return $this->update($pathToFile, $updatedContent);
    }

    public function prepend(string $pathToFile, string $content): bool
    {
        $fileContents = file_get_contents($pathToFile);
        $isSuccess = file_put_contents($pathToFile, $content . "\n" . $fileContents);

        return is_int($isSuccess);
    }

    public function append(string $pathToFile, string $content): bool
    {
        $isSuccess = file_put_contents($pathToFile, $content . "\n", FILE_APPEND | LOCK_EX);

        return is_int($isSuccess);  
    }

    public function createIfNotExists(string $pathToFile, bool $recursive = false): bool
    {
        try {
            $this->exists($pathToFile);
            return true;
        } catch (FileNotFoundException $e) {
            $dir = pathinfo($pathToFile)['dirname'];

            if (!is_dir($dir) && $recursive) {
                mkdir($dir, 0755, true);
            }

            return is_int(file_put_contents($pathToFile, ''));
        }
    }

    /** @throws FileNotFoundException */
    public function getLastLines(string $pathToFile, int $lineNumber = 1): ?string
    {
        $this->exists($pathToFile);
        $file = escapeshellarg($pathToFile);
        $lines = `tail -n $lineNumber $file`;

        return $lines;
    }

    /** @throws FileNotFoundException */
    public function getAfterLine(string $pathToFile, int $lineNumber, int $limit = -1): array
    {
        $this->exists($pathToFile);

        $file = new LimitIterator(
            new SplFileObject($pathToFile),
            $lineNumber,
            $limit
        );

        $lines = [];
        foreach ($file as $line => $content) {
            $lines[$line] = $content;
        }

        return $lines;
    }

    /** @throws FileNotFoundException */
    public function getFirstLine(string $pathToFile): string
    {
        return str_replace("\n", '', $this->getAfterLine($pathToFile, 0, 1)[0]);
    }

    public function fileContains(string $pathToFile, array $content): bool
    {
        $fileContent = $this->getAfterLine($pathToFile, 0);
        $expectedCount = count($content);
        $count = 0;

        foreach ($fileContent as $lineContent) {
            foreach ($content as $string) {
                // check also json format
                $wordExistsInLine = (str_contains($lineContent, json_encode($string))) || str_contains($lineContent, $string);
                if ($wordExistsInLine) {
                    $count++;
                }

                if ($expectedCount === $count) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param string $to KB/MB/GB */
    public function convertSizeFromBytes(int $bytes, string $to, int $precision = 2): float
    {
        $formulas = [
            'KB' => number_format($bytes / 1024, $precision),
            'MB' => number_format($bytes / 1048576, $precision),
            'GB' => number_format($bytes / 1073741824, $precision),
        ];
        return isset($formulas[$to]) ? $formulas[$to] : 0;
    }
}
