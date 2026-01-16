<?php

namespace Tests\Unit\Wordpress\Wp_content\Plugins\Lotto_platform\Includes\Lotto\Platform\Casino;

use Closure;
use Helpers_App;
use Lotto_Platform;

final class NavigationFilterTest extends \Test_Unit
{
    private Lotto_Platform $platform;

    public function setUp(): void
    {
        if (!defined('WPINC')) {
            define('WPINC', 'defined');
        }
        if (!class_exists('Lotto_Platform')) {
            require Helpers_App::get_absolute_file_path('wordpress/wp-content/plugins/lotto-platform/includes/Lotto/Platform.php');
        }

        $_SERVER['HTTP_HOST'] = 'casino.lottopark.loc';

        $this->platform = new class extends Lotto_Platform
        {
            public function getCasinoNavigationFilterMocked(): Closure // trick to not declare public. Alternatively can be done via phpunit or reflection.
            {
                return $this->getCasinoNavigationFilter();
            }
        };

        if (!function_exists('lotto_platform_get_permalink_by_slug')) {
            eval("
                function lotto_platform_get_permalink_by_slug(string \$slug) {
                    \Test_Unit::assertSame('play', \$slug);
                    return 'https://casino.lovcasino.com/lottery/play/';
                }            
                ");
        }
    }

    /** @test */
    public function validItems_Array_Success(): void
    {
        $items = [
            'https://casino.lovcasino.com/lottery/play/',
            'https://casino.lovcasino.com/casino-play/',
            'https://casino.lovcasino.com/',
            'https://www.casino.lovcasino.com/',
            'https://www.casino.lovcasino.com/',
        ];
        $navigationFilter = $this->platform->getCasinoNavigationFilterMocked();
        $filteredNavItems = $navigationFilter($items);

        $this->assertSame('https://lovcasino.com/lottery/play/', $filteredNavItems[0]); // strip casino
        $this->assertSame('https://casino.lovcasino.com/casino-play/', $filteredNavItems[1]); // no change
        $this->assertSame('https://casino.lovcasino.com/', $filteredNavItems[2]); // no change
        $this->assertSame('https://casino.lovcasino.com/', $filteredNavItems[3]); // strip www
    }

    /** @test */
    public function validItems_String_Success(): void
    {
        $items = "
            https://casino.lovcasino.com/lottery/play/
            https://casino.lovcasino.com/casino-play/
            https://casino.lovcasino.com/
            https://www.casino.lovcasino.com/ 
        ";
        $navigationFilter = $this->platform->getCasinoNavigationFilterMocked();
        $filteredNavItems = $navigationFilter($items);
        $filteredNavItems = explode("\n", $filteredNavItems);

        $this->assertSame('https://lovcasino.com/lottery/play/', trim($filteredNavItems[1])); // strip casino
        $this->assertSame('https://casino.lovcasino.com/casino-play/', trim($filteredNavItems[2])); // no change
        $this->assertSame('https://casino.lovcasino.com/', trim($filteredNavItems[3])); // no change
        $this->assertSame('https://casino.lovcasino.com/', trim($filteredNavItems[4])); // strip www
    }
}
