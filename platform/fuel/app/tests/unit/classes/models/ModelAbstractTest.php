<?php

namespace Unit\Classes\Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelLottery;
use SebastianBergmann\FileIterator\Facade;
use Test_Unit;
use Models\LotteryProvider;

/**
 * Class ModelAbstract
 * @package Unit\Classes\Models
 *
 * This test checks: casts to carbon, set correct timezone while casting, get and set properties in camelCase, new property relations in model
 */
class ModelAbstractTest extends Test_Unit
{
    private AbstractOrmModel $model;

    /** @test  */
    public function timezone__without_casting__return_utc()
    {
        $this->createSimpleObject();

        /** @var Carbon $date */
        $date = $this->model->date;
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertSame('UTC', $date->timezoneName);
        $this->assertSame($date->timezoneName, $this->model->getTimezoneForField('date'));
    }

    /** @test  */
    public function timezone__self_casting__return_warsaw()
    {
        $this->createSingleRelationObject();

        /** @var Carbon $date */
        $date = $this->model->date;
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertSame('Europe/Warsaw', $date->timezoneName);
        $this->assertSame($date->timezoneName, $this->model->getTimezoneForField('date'));
    }

    /** @test  */
    public function timezone__relation_casting__return_america()
    {
        $this->createDeepRelationObject();

        /** @var Carbon $date */
        $date = $this->model->date;
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertSame('America/New_York', $date->timezoneName);
        $this->assertSame($date->timezoneName, $this->model->getTimezoneForField('date'));
    }

    private function createSimpleObject()
    {
        $this->model = new class () extends AbstractOrmModel
        {
            protected static string $_table_name = 'lottery_log';

            protected static array $_properties = [
                'id',
                'date' => ['default' => '2021-02-12 12:00:00'],
                'message',
                'timezone' => ['default' => 'Europe/Warsaw']
            ];

            protected $casts = [
                'id' => 'integer',
                'message' => 'string',
                'date' => 'carbon',
                'timezone' => 'string'
            ];

            protected array $relations = [];

            protected array $timezones = [];
        };
    }

    private function createSingleRelationObject()
    {
        $this->model = new class () extends AbstractOrmModel
        {
            protected static string $_table_name = 'lottery_log';

            protected static array $_properties = [
                'id',
                'date' => ['default' => '2021-02-12 12:00:00'],
                'message',
                'timezone' => ['default' => 'Europe/Warsaw']
            ];

            protected $casts = [
                'id' => 'integer',
                'message' => 'string',
                'date' => 'carbon',
                'timezone' => 'string'
            ];

            protected array $relations = [
                WhitelabelLottery::class => self::BELONGS_TO
            ];

            protected array $timezones = [
                'date' => ['timezone']
            ];
        };

        $whitelabel_lottery = new WhitelabelLottery();
        $whitelabel_lottery->id = 1;
        $whitelabel_lottery->timezone = 'Europe/Paris';
        $this->model->whitelabelLottery = $whitelabel_lottery;
        $this->model->whitelabelLotteryId = 1;
    }

    private function createDeepRelationObject()
    {
        $this->model = new class () extends AbstractOrmModel
        {
            protected static string $_table_name = 'lottery_log';

            protected static array $_properties = [
                'id',
                'date' => ['default' => '2021-02-12 12:00:00'],
                'message',
                'timezone' => ['default' => 'Europe/Warsaw']
            ];

            protected $casts = [
                'id' => 'integer',
                'message' => 'string',
                'date' => 'carbon',
                'timezone' => 'string'
            ];

            protected array $relations = [
                WhitelabelLottery::class => self::BELONGS_TO
            ];

            protected array $timezones = [
                'date' => [WhitelabelLottery::class, LotteryProvider::class, 'timezone']
            ];
        };

        $lotteryProvider = new LotteryProvider();
        $lotteryProvider->timezone = 'America/New_York';
        $lotteryProvider->id = 1;
        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->timezone = 'Europe/Paris';
        $whitelabelLottery->id = 1;
        $whitelabelLottery->lotteryProvider = $lotteryProvider;
        $whitelabelLottery->lotteryProviderId = 1;
        $this->model->whitelabelLottery = $whitelabelLottery;
        $this->model->whitelabelLotteryId = 1;
    }

    /** @test  */
    public function model__in_models__should_contains_relation_properties()
    {
        $modelFiles = [];
        $modelsPath = APPPATH . 'classes' . DS . 'models';
        $oldModelsPath = APPPATH . 'classes' . DS . 'model';
        $filesystem = new Facade();
        $modelFiles += $filesystem->getFilesAsArray($modelsPath);
        $modelFiles += $filesystem->getFilesAsArray($oldModelsPath);

        foreach ($modelFiles as $filename) {
            if (!is_file($filename)) {
                continue;
            }

            if (!str_contains($filename, '.php')) {
                continue;
            }

            $classContent = file_get_contents($filename);

            if (!str_contains($classContent, 'extends AbstractOrmModel')) {
                continue;
            }

            if (!str_contains($classContent, 'protected array $relations = [')) {
                continue;
            }

            $this->assertStringContainsString('protected static array $_belongs_to = [', $classContent);
            $this->assertStringNotContainsString('//    protected static array $_belongs_to = [', $classContent);
            $this->assertStringContainsString('protected static array $_has_one = [', $classContent);
            $this->assertStringNotContainsString('//    protected static array $_has_one = [', $classContent);
            $this->assertStringContainsString('protected static array $_has_many = [', $classContent);
            $this->assertStringNotContainsString('//    protected static array $_has_many = [', $classContent);
        }
    }
}
