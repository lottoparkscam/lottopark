<?php

namespace Services;

class ShellExecutorService
{
    /** @return string|false|null */
    public function execute(string $command)
    {
        return shell_exec($command);
    }
}