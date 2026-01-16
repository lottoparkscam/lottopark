<?php

namespace Feature\Generic;

use Container;
use Fuel\Core\Config as FuelConfig;
use Test_Feature;
use Wrappers\Config as ConfigWrapper;
use Wrappers\Decorators\Config as ConfigDecorator;
use Wrappers\Decorators\ConfigContract;

class ContainerTest extends Test_Feature
{
    public function setUp(): void
    {
        parent::setUp();
        ob_start();
    }

    /** @test */
    public function forge__asSingleton__returns_same_instance(): void
    {
        // Given
        $asSingleton = true;

        // When
        $expected = Container::forge($asSingleton);
        $actual = Container::forge($asSingleton);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function forge__asSingleton__by_default_returns_same_instance(): void
    {
        // When
        $expected = Container::forge();
        $actual = Container::forge();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function forge__not_asSingleton__by_different_instances(): void
    {
        // When
        $expected = Container::forge(false);
        $actual = Container::forge(false);

        // Then
        $this->assertNotSame($expected, $actual);
    }

    /** @test */
    public function config_wrapper__with_env_different_than_prod__returns_wrapper_with_warning(): void
    {
        // Given
        Container::forge()->set('env', 'dev');
        $expected_class_name = ConfigWrapper::class;
        $expected_warning_message = 'Attempted to load Fuel core config in some class. It is recommended to use ConfigContract. Check container-config.php';

        // When
        $actual = get_class(Container::get(ConfigWrapper::class));
        $ob = ob_get_contents();

        // Then
        $this->assertSame($expected_class_name, $actual);
        $this->assertSame($expected_warning_message, $ob);
    }

    /** @test */
    public function config_wrapper__with_prod_env__returns_fuels_config_without_warning(): void
    {
        // Given
        Container::forge()->set('env', 'prod');
        $expected_class_name = ConfigWrapper::class;
        $expected_warning_message = '';

        // When
        $actual = get_class(Container::get(ConfigWrapper::class));
        $ob = ob_get_contents();

        // Then
        $this->assertSame($expected_class_name, $actual);
        $this->assertSame($expected_warning_message, $ob);
    }

    /** @test */
    public function config__by_interface__returns_decorator(): void
    {
        // Given
        $expected_class_name = ConfigDecorator::class;

        // When
        $actual = get_class(Container::get(ConfigContract::class));

        // Then
        $this->assertSame($expected_class_name, $actual);
    }

    /** @test */
    public function fuels_config__with_env_different_than_prod__returns_fuels_config_with_warning(): void
    {
        // Given
        Container::forge()->set('env', 'dev');
        $expected_class_name = FuelConfig::class;
        $expected_warning_message = 'Attempted to load Fuel core config in some class. It is recommended to use ConfigContract. Check container-config.php';

        // When
        $actual = get_class(Container::get(FuelConfig::class));
        $ob = ob_get_contents();

        // Then
        $this->assertSame($expected_class_name, $actual);
        $this->assertSame($expected_warning_message, $ob);
    }

    /** @test */
    public function fuels_config__with_prod_env__returns_fuels_config_without_warning(): void
    {
        // Given
        Container::forge()->set('env', 'production');
        $expected_class_name = FuelConfig::class;
        $expected_warning_message = '';

        // When
        $actual = get_class(Container::get(FuelConfig::class));
        $ob = ob_get_contents();

        // Then
        $this->assertSame($expected_class_name, $actual);
        $this->assertSame($expected_warning_message, $ob);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        ob_clean();
        ob_end_clean();
    }
}
