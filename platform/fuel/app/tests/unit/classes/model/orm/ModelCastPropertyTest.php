<?php

namespace Tests\Unit\Classes\Model\Orm;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Test_Unit;

final class ModelCastPropertyTest extends Test_Unit
{
    private AbstractOrmModel $fakeModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->createFakeModel();
    }

    /**
     * @test
     * @dataProvider fakeModelDataProvider
     * @param string $property
     * @param mixed $value
     * @param mixed $expected
    */
    public function propertyCast_isValid(string $property, mixed $value, mixed $expected): void
    {
        $this->fakeModel->$property = $value;

        $this->assertSame($expected, $this->fakeModel->$property);
    }

    /** @test */
    public function dateTimeProperty_ShouldBeFuelDateObject(): void
    {
        $this->fakeModel->created_at = '2022-04-04 12:00:00';
        /** Cast for taken value is from AbastracOrmModel */
        $this->assertInstanceOf(\Fuel\Core\Date::class, $this->fakeModel->created_at);
    }

    /** @test */
    public function carbonProperty_ShouldBeCarbonObject(): void
    {
        $this->fakeModel->last_update = '2022-04-04 12:00:00';
        /** Cast for taken value is from AbastracOrmModel */
        $this->assertInstanceOf(Carbon::class, $this->fakeModel->last_update);
    }

    public function fakeModelDataProvider(): array
    {
        return [
            ['id', 1, 1],
            ['is_enabled', true, true],
            ['is_enabled', 1, true],
            ['is_enabled', 0, false],
            ['data', ['example' => 'example'], ['example' => 'example']],
            ['data', 'tests', 'tests'],
            ['data', 12345, 12345],
            ['last_update', null, null],
            ['name', 'fake', 'fake'],
            ['amount', 10.00, 10.00],
            ['amount', 10, 10.00],
            ['amount', '10', 10.00],
        ];
    }

    private function createFakeModel(): void
    {
        $this->fakeModel = new class () extends AbstractOrmModel
        {
            protected static string $_table_name = 'fake_model';

            protected static array $_properties = [
                'id',
                'is_enabled' => ['default' => false],
                'data',
                'name',
                'created_at' => ['default' => '2022-04-04 12:00:00'],
                'last_update',
                'amount' => ['default' => 0.00],
            ];

            protected $casts = [
                'id' => self::CAST_INT,
                'is_enabled' => self::CAST_BOOL,
                'data' => self::CAST_ARRAY,
                'name' => self::CAST_STRING,
                'created_at' => self::CAST_DATETIME,
                'last_update' => self::CAST_CARBON,
                'amount' => self::CAST_FLOAT,
            ];

            protected array $relations = [];
            protected array $timezones = [];
        };
    }
}
