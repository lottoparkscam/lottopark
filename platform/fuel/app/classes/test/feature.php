<?php

declare(strict_types=1);

use Fuel\Core\Session;
use Models\WhitelabelUser;
use Orm\Model;
use Fuel\Core\DB;
use Models\Whitelabel;
use Fuel\Core\Autoloader;

Autoloader::add_namespace('Fuel\\Tasks\\Factory\\Utils', APPPATH . 'tasks/factory/utils/');

/** Base class for feature tests. */
abstract class Test_Feature extends Test_Base
{
    protected \DI\Container $container;

    /**
     * By default we build container with first found WL in DB,
     * but you can replace it here for mocking or any other purpose.
     *
     * @var Whitelabel|null
     */
    protected ?Whitelabel $contextWhitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = Container::forge(false);
        $wl = $this->contextWhitelabel ?? Whitelabel::query()->get_one();
        $this->container->set('whitelabel', $wl);
        $this->container->set('domain', $wl->domain);
    }

    /**
     * Set to true if every test method in test class should be run in database transaction
     * which will be rolled back after test method is done.
     *
     * @var boolean
     */
    protected $in_transaction = true;

    public function skip_due_no_expected_data_retrieved(string $message = null): void
    {
        if (!$message) {
            $this->markTestIncomplete(sprintf('Test <%s> has been skipped due there was no expected data from external resource (LCS probably)', get_called_class()));
        }
        $this->markTestIncomplete($message);
    }

    public function skip(?string $message = null): void
    {
        $this->markTestIncomplete($message);
    }

    public function skip_on_production_or_staging_env(): void
    {
        if (Helpers_App::is_staging_environment() || Helpers_App::is_production_environment()) {
            $this->markTestSkipped('Skipped due env is production or test');
        }
    }

    protected function assertDbHasExpectedQueryCount(int $expected = 0): void
    {
        $this->assertEquals($expected, DB::count_last_query());
    }

    /**
     * @param Orm\Model|string $model
     * @param array<string, string, mixed>|array<array<string, string, mixed>> $where
     * @param ?int $count - if null passed, method checks only existence of at least one row
     */
    protected function assertDbHasRows($model, array $where = [], ?int $count = null): void
    {
        if (is_string($model)) {
            $model = $model::forge();
        }

        $model::flush_cache();
        $actual = $model::query()->where($where)->count();

        if (is_null($count)) {
            $this->assertGreaterThanOrEqual(1, $actual);
            return;
        }
        $whereAsJson = json_encode($where);
        $modelName = get_class($model);
        $this->assertSame($count, $actual, "DB for '$modelName' rows with where: '$whereAsJson' should contain exactly: $count rows. Actually are: $actual");
    }

    protected static function truncate(string ...$models): void
    {
        $extractTableName = function(string $model): string {
            /** @var Model $instance */
            $instance = $model::forge();
            $reflection = new ReflectionProperty($instance, '_table_name');
            $reflection->setAccessible(true);
            return $reflection->getValue();
        };

        $tables = [];
        foreach($models as $model) {
            $tables[] = $extractTableName($model);
        }

        if (empty($tables)) {
            return;
        }

        $tables = implode(',', $tables);
        DB::query("SET foreign_key_checks = 0; TRUNCATE $tables; SET foreign_key_checks = 1;")->execute();
    }

    public function setUserAsCurrentInSession(WhitelabelUser $user): void
    {
        $whitelabel = Container::get('whitelabel');
        $user->whitelabelId = $whitelabel->id;
        $user->isDeleted = false;
        $user->save();

        Session::set('user.id', $user->id);
        Session::set('user.email', $user->email);
        Session::set('user.token', $user->token);
        Session::set('user.hash', $user->hash);
        Session::set('user.remember', 1);
        Session::set('is_user', 1);
    }

    public function removeUserFromSession(): void
    {
        Session::delete('user');
        Session::delete('is_user');
    }
}
