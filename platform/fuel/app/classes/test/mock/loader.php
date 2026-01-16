<?php

use Helpers\ArrayHelper;

/**
 * Allow to mock final classes by custom loading.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-24
 * Time: 20:10:59
 */
final class Test_Mock_Loader
{

    /**
     * Parse fuel namespace class name to file path.
     *
     * @param string $class
     *
     * @return string
     */
    private static function fuel_to_file(string $class): string
    {
        $path_base = APPPATH;
        $reflection = new ReflectionClass($class);
        if ($reflection->getNamespaceName() === "Fuel\\Core") {
            $path_base = COREPATH;
        }
        $file_path = strtolower(ArrayHelper::last(explode('\\', $class)));

        return $path_base . 'classes' . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $file_path) . '.php';
    }

    /**
     * Load final class as non final.
     * Also change callbacks to be child scope based.
     * NOTE: if class under alias already exists it will terminate prematurely.
     *
     * @param string $class
     * @param string $alias alias under which class should be loaded.
     *
     * @return void
     */
    public static function load_class_as_mockable(string $class, string $alias = null): void
    {
        // silently terminate if alias is already taken
        if (class_exists($alias)) {
            return;
        }
        // convert fuel namespace to file path
        $file_name = self::fuel_to_file($class);
        // load file content
        $class_content = file_get_contents($file_name);
        // remove <?php
        $class_content = substr($class_content, 5);
        // rename if specified
        if ($alias !== null) {
            $class_content = preg_replace('/class \\w*/', "class $alias", $class_content, 1);
        }
        // replace final (stop on first from beginning)
        // could be done with preg_replace limit, but these files shouldn't be big
        $class_content = str_replace('final class', 'class', $class_content);
        // replace private to protected, so everything can be overriden
        $class_content = str_replace('private', 'protected', $class_content);
        $class_content = str_replace('self', 'static', $class_content);
        // remove namespace if there is any
        $class_content = preg_replace('/namespace/', "// namespace", $class_content, 1);
        // load into scope
        eval($class_content);
    }
}
