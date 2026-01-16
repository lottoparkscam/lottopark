<?php

namespace Services;

class WordpressApiService
{
    /**
     * Provided url path: /api/test/test1/test2
     * Extracts namespace + class: Services\WordpressApi\Test\Test1
     * Function: Test2()
     *
     * @param string $path
     */
    public function run(string $path)
    {
        $pathWithoutFirstSlash = str_replace('/api/', '', $path);
        $pathChunks = explode('/', $pathWithoutFirstSlash);
        $namespace = "Services\WordpressApi";
        $pathCount = count($pathChunks);

        http_response_code(200);

        foreach ($pathChunks as $index => $chunk) {
            $isNotLast = $pathCount - 1 !== $index;

            if ($isNotLast) {
                $chunk = ucfirst($chunk);
                $namespace .= "\\$chunk";
                continue;
            }

            if (!class_exists($namespace)) {
                http_response_code(404);
                exit();
            }

            echo (new $namespace())->$chunk();
        }

        exit();
    }
}
