<?php

use Fuel\Core\Cache;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Fuel\Core\Fuel;
use Orm\Query;
use PHPUnit\Framework\TestCase;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Wrappers\Decorators\ConfigContract;

abstract class Test_Base extends TestCase
{

    /**
     * Set to true if every test method in test class should be run in database transaction
     * which will be rolled back after test method is done.
     *
     * @var boolean
     */
    protected $in_transaction = false;

    /**
     * If test is running in transaction after rollback
     * tables stored here will have auto-increment reset.
     *
     * @var array
     */
    protected $tables_to_rewind = [];

    /**
     * NOTE: this variable hold model classes.
     * If test is running in transaction after rollback
     * tables stored here will have auto-increment reset.
     *
     * @var array
     */
    protected $models_to_rewind = [];

    /**
     * Documented linkage to php unit assertTrue method.
     *
     * @param mixed $condition condition to be checked. IMPORTANT: comparison to true is strict.
     * @return void
     */
    public static function assertTrue($condition, $message = ''): void
    {
        parent::assertTrue($condition, $message);
    }

    /**
     * Define operations before every test class method.
     */
    public function setUp(): void
    {
        Query::caching(false);

        parent::setUp();
        Fuel::$env = Fuel::TEST;
        $_ENV['SERVER_TYPE'] = FUEL::TEST;
        if ($this->in_transaction) {
            DB::start_transaction();
        }
    }

    /**
     * Rewind auto increment for specified table.
     *
     * @param string $table_name
     * @return void
     */
    private function rewind_table_auto_increment(string $table_name): void
    {
        DB::query("ALTER TABLE $table_name AUTO_INCREMENT = 0")
            ->execute();
    }

    /**
     * Define operations after every test class method.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->in_transaction) {
            DB::rollback_transaction(); // NOTE: this will be called even if test encountered unexpected errors
            // rewind id's for used tables.
            foreach ($this->models_to_rewind as $model) {
                $model_reflection = new ReflectionClass($model);
                $table_name_property = $model_reflection->getProperty('_table_name');
                $table_name_property->setAccessible(true);
                $this->rewind_table_auto_increment($table_name_property->getValue(null));
            }
            foreach ($this->tables_to_rewind as $table) {
                $this->rewind_table_auto_increment($table);
            }
        }

        Cache::delete_all();
        $this->clearLogFiles();
    }

    protected function clearLogFiles(): void
    {
        /** @var ConfigContract $configContract */
        $configContract = Container::get(ConfigContract::class);
        $logFilePath = $configContract->get('log_path');

        $logDirDoesNotExist = !is_dir($logFilePath);
        if ($logDirDoesNotExist) {
            return;
        }

        $isNotTestDir = !str_contains($logFilePath, 'tests'); // should never happen
        if ($isNotTestDir) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($logFilePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($logFilePath);
    }

    /**
     * Use this function after testing method with Input::() 
     * Resetting global variables like $_GET does not have impact on Input::get();
     */
    protected function resetInput(): void
    {
        $availableMethods = ['get', 'post', 'patch', 'put', 'delete'];

        foreach ($availableMethods as $method) {
            $methodParams = array_keys(Input::$method());
            if (empty($methodParams)) {
                continue;
            }
            foreach ($methodParams as $param) {
                // forge doesn't have unset method, thats why we have to empty the values
                Input::forge()->_set(strtoupper($method), [$param => null]);
            }
        }
    }

    /**
     * @param array $parameters example: ['parameterName' => 'parameterValue'];
     * 
     * Use this function for testing functions with Input::();
     * Setting directly $_GET doesn't have impact on Input::get() class
     */
    protected function setInput(string $method, array $parameters, bool $withCsrfToken = false): void
    {
        if ($withCsrfToken) {
            $csrfTokenKey = Config::get('security.csrf_token_key');
            $csrfToken = Security::fetch_token();
            $_COOKIE[$csrfTokenKey] = $csrfToken;
            Security::_init();
            $parameters[$csrfTokenKey] = $csrfToken;
        }
        Input::forge()->_set($method, $parameters);
    }
}
