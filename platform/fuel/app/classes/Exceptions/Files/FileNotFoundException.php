<?php

namespace Exceptions\Files;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $pathToFile)
    {
        parent::__construct('Cannot find file with provided path: ' . $pathToFile);
    }
}
