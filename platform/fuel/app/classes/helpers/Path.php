<?php

class Path
{
    /**
     * Unify path between Operation Systems
     * @param string $path
     * @return string
     */
    public static function unifyPath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}