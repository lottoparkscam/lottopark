<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\Wordpress\PageHelper;
use Test_Unit;

class PageHelperTest extends Test_Unit
{
    /** @test */
    public function isNewsPost_isNot(): void
    {
        $_SERVER['REQUEST_URI'] = '/asd/qwe';
        $actual = PageHelper::isNewsMainPage();
        $this->assertFalse($actual);
    }

    /** @test */
    public function isNewsPost(): void
    {
        $_SERVER['REQUEST_URI'] = '/news';
        $actual = PageHelper::isNewsMainPage();
        $this->assertTrue($actual);
    }

    /** @test */
    public function isNotNewsPost(): void
    {
        $_SERVER['REQUEST_URI'] = '/asd';
        $actual = PageHelper::isNewsMainPage();
        $this->assertFalse($actual);
    }

    /** @test */
    public function isFeedPage(): void
    {
        $_SERVER['REQUEST_URI'] = '/feed';
        $actual = PageHelper::isFeedPage();
        $this->assertTrue($actual);
    }

    /** @test */
    public function isNotFeedPage(): void
    {
        $_SERVER['REQUEST_URI'] = '/afasd';
        $actual = PageHelper::isNotFeedPage();
        $this->assertTrue($actual);
    }

    /**
     * @test
     * @dataProvider isAnyOrderPageProvider
     */
    public function isAnyOrderPage(string $path, bool $expected): void
    {
        $_SERVER['REQUEST_URI'] = $path;
        $actual = PageHelper::isAnyOrderPage();
        $this->assertSame($expected, $actual);
    }

    public function isAnyOrderPageProvider(): array
    {
        return [
            'confirm order' => ['/order/confirm/123/', true],
            'some path with order' => ['/order/asd/', true],
            'order somewhere in the path' => ['/asd/order/asd/123/', true],
            'path without order' => ['/asd/qwe/asd/123/', false],
        ];
    }

    /**
     * @test
     * @dataProvider isNotMainPageWithQueryProvider
     */
    public function isNotMainPageWithQuery(string $requestUri, bool $expected): void
    {
        $_SERVER['REQUEST_URI'] = $requestUri;
        $actual = PageHelper::isNotMainPageWithQuery();
        $this->assertSame($expected, $actual);
    }

    public function isNotMainPageWithQueryProvider(): array
    {
        return [
          'news query' => ['/?post_type=post', false],
          'feed query' => ['/?feed=rss2', false],
          'main page' => ['/', true],
          'other page with trailing slash' => ['/test/', true],
          'other page without trailing slash' => ['/test', true],
          'other page with trailing slash and query' => ['/test/?abc=test', true],
          'other page without trailing slash and query' => ['/test?abc=test', true],
        ];
    }
}
