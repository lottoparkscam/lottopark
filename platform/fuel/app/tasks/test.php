<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Helper_File;
use Helper_Print;
use Helpers_Cli;
use ReflectionClass;
use ReflectionMethod;
use Task_Cli;

final class Test extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    /**
     * Help for the task.
     */
    public function help(): void // TODO: {Vordis 2020-03-27 12:35:01} extract to higher layer
    {
        $class = new ReflectionClass($this);
        $helpMessage = '';
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $helpMessage .= 'php oil r test:' . $method->getName() . ' - ' . $method->getDocComment() . "\r\n";
        }
        echo $helpMessage;
    }

    private function execute_tests(string $arguments): void
    {
        $phpunit_binary_path = VENDORPATH . Helper_File::build_path('bin', 'phpunit');
        $configuration_path = APPPATH . 'phpunit.xml';
        $command = "$phpunit_binary_path $arguments --verbose --testdox --configuration $configuration_path";
        Helper_Print::echonl("Launch command: $command" . PHP_EOL);
        Helpers_Cli::execute_or_fail_with_print_output($command);
    }

    /**
     * Run all tests in project.
     */
    public function run(): void
    {
        $this->execute_tests('--testsuite app');
    }

    private function build_destination_arguments(String $test_folder): string
    {
        $path = APPPATH . Helper_File::build_path('tests', $test_folder);
        $destination = Cli::option(3);
        $full_path = Helper_File::build_path($path, "$destination.php");
        $filter = Cli::option('filter', null);
        if ($filter !== null) {
            $filter = "--filter $filter";
        }

        return "$full_path $filter";
    }

    /**
     * Run all unit tests (suite)
     * e.g. php oil r test:units
     */
    public function units(): void
    {
        $this->execute_tests('--testsuite unit');
    }

    /**
     * Run all unit tests under file or directory.
     * e.g. php oil r test:single
     */
    public function unit(): void
    {
        $this->execute_tests($this->build_destination_arguments('unit'));
    }

    /**
     * Run all unit tests under file or directory.
     * e.g. `php oil r test:feature foo --filter foo` will launch test under app\tests\feature\foo.php filtered to foo methods.
     * NOTE: filter is optional.
     */
    public function feature(): void
    {
        $this->execute_tests($this->build_destination_arguments('feature'));
    }
}
