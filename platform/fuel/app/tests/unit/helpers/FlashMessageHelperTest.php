<?php

namespace Tests;

use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Test_Unit;

class FlashMessageHelperTest extends Test_Unit
{
    public function setUp(): void
    {
        Session::delete('message');
        Session::delete_flash('message');
        parent::setUp();
    }

    public function tearDown(): void
    {
        Session::delete('message');
        Session::delete_flash('message');
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider getLastProvider
     */
    public function getLast(array $expectedMessages, bool $byFlash = false): void
    {
        foreach ($expectedMessages as $expected) {
            if ($expected) {
                $isGlobal = !$byFlash;
                FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, $expected, $isGlobal);
            }
        }

        $actual = FlashMessageHelper::getLast();
        $this->assertSame(end($expectedMessages), $actual);
    }

    /** @test */
    public function remove(): void
    {
        Session::set('message', 'asdassdadsadasdadasd');
        Session::set_flash('message', 'asdassdadsadasdadasd');
        FlashMessageHelper::remove();
        $this->assertEmpty(Session::get_flash('message'));
        $this->assertEmpty(Session::get('message'));
    }

    public function getLastProvider(): array
    {
        return [
            'Set flash message' => [['Example message'], false],
            'Set multiple flash message' => [['Example message', 'Last flash message'], false],
            'Set global message' => [['Example global message'], true],
            'Set multiple global message' => [['Example global message', 'Last global message'], true],
            'No message - flash' => [[''], false],
            'No message - global' => [[''], true],
        ];
    }
}
