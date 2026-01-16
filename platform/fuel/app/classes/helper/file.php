<?php

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2020-05-15
 * Time: 10:26:00
 */
final class Helper_File
{
    /**
     * @param string $paths if specified concatenate to app path
     * @return string app path with trailing dir separator
     */
    public static function app_path(string ...$paths): string
    {
        $result_path = APPPATH . array_shift($paths);
        foreach ($paths as $path) {
            $result_path .= DIRECTORY_SEPARATOR . $path;
        }
        return $result_path;
    }

    public static function build_path(string ...$segments): string
    {
        $path_beginning = array_shift($segments);
        if (empty($segments)) {
            return $path_beginning;
        }
        $path_end = DIRECTORY_SEPARATOR . array_pop($segments);
        $path_middle = '';
        foreach ($segments as $segment) {
            $path_middle .= DIRECTORY_SEPARATOR . $segment;
        }
        return "$path_beginning$path_middle$path_end";
    }
}
