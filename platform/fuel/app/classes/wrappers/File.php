<?php


namespace Wrappers;

/**
 * @codeCoverageIgnore
 */
class File
{
    public function exists($path, $area = null): bool
    {
        return \Fuel\Core\File::exists($path, $area);
    }
}
