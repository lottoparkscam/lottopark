<?php

use Fuel\Core\Cli;

abstract class Task_Cli
{
    /**
     * Automatically creates and prints help block (based on task class methods).
     */
    public function help(): void // TODO: {Vordis 2020-03-27 12:35:01} extract to higher layer
    {
        $child_class_name = static::class;
        $child_file_name = strtolower(substr($child_class_name, strrpos($child_class_name, '\\') + 1)); // cut last part of the class name and lowercase it
        $class = new ReflectionClass($child_class_name);
        $helpMessage = '';
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $helpMessage .= "php oil r $child_file_name:" . $method->getName() . PHP_EOL . '     ' . $method->getDocComment() . "\r\n";
        }
        echo $helpMessage;
    }

    /**
     * Confirms task execution in production environment by asking user for input "y|yes" or "n|no".
     * Input case insensitive.
     */
    public function productionSafetyConfirm(): void
    {
        if (Helpers_App::is_production_environment()) {
            switch (strtolower(Cli::prompt('Warning! You are in production env! Are you sure you want to execute this task? y/n'))) {
                case 'y':
                case 'yes':
                    // proceed
                    return;
                    break;

                case 'n':
                case 'no':
                    exit("Task aborted on user's request.\r\n");
                    break;

                default:
                    exit("Wrong input provided. Action aborted. Run again last command and use y/yes to confirm and n/no to abort.\r\n");
            }
        }
    }

    /**
     * Permanently disable task execution in production environment.
     */
    public function disableOnProduction(): void
    {
        if (Helpers_App::is_production_environment()) {
            exit("Disabled on production environment\r\n");
        }
    }
}
