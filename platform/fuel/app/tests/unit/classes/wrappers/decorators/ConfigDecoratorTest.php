<?php

namespace Tests\Unit\Wrappers\Decorators;

use Test_Unit;
use Wrappers\Config as ConfigWrapper;
use Wrappers\Decorators\Config;

class ConfigDecoratorTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider valuesDataProvider
     *
     * @param mixed $value
     * @param mixed $expected_value
     * @param mixed $expected_type
     */
    public function getValues__CastsToProperBoolType($value, $expected_value, $expected_type): void
    {
        // Given
        $config = $this->createMock(ConfigWrapper::class);
        $config->
            expects($this->once())
            ->method('get')
            ->willReturn($value);

        // When & Given
        $decorator = new Config($config);
        $actual = $decorator->get('item');
        $actual_type = gettype($actual);

        // Then
        $this->assertSame($expected_value, $actual);
        $this->assertSame($expected_type, $actual_type);
    }

    public function valuesDataProvider(): array
    {
        return [
            'true as string' => ['true', true, 'boolean'],
            'false as string' => ['false', false, 'boolean'],
            'random string' => ['sadasd', 'sadasd', 'string'],
            'number as string' => ['123', '123', 'string'],
            'number' => [123, 123, 'integer'],
        ];
    }

    /** @test */
    public function set__CallsWrapperMethod(): void
    {
        // Given
        $key = 'key';
        $value = 'some val';

        $config = $this->createMock(ConfigWrapper::class);
        $config->
        expects($this->once())
            ->method('set')
            ->with($key, $value);

        // When
        $decorator = new Config($config);
        $decorator->set($key, $value);
    }
}
