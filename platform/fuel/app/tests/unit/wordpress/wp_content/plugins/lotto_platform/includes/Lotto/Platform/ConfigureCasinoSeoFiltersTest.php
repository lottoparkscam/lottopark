<?php

namespace Tests\Unit\Wordpress\Wp_content\Plugins\Lotto_platform\Includes\Lotto\Platform;

use Helpers_App;
use Lotto_Platform;
use Models\Whitelabel;
use Test\Unit\Wordpress\Wp_content\Plugins\Lotto_platform\Includes\Lotto\Platform\ConfigureCasinoSeoFiltersFixtures;

if (!defined('WPINC')) {
    define('WPINC', 'defined');
}
if (!class_exists('Lotto_Platform')) {
    require Helpers_App::get_absolute_file_path('wordpress/wp-content/plugins/lotto-platform/includes/Lotto/Platform.php');
}

final class ConfigureCasinoSeoFiltersTest extends \Test_Unit
{
    private Lotto_Platform $platform;

    public function setUp(): void
    {
        $this->platform = new class extends Lotto_Platform {
            public function configureCasinoSeoFiltersMocked(Whitelabel $whitelabel): void // trick to not declare public. Probably can be done with framework
            {
                $this->configureCasinoSeoFilters($whitelabel);
            }
        };
    }

    /** @test */
    public function invalidPage__ReturnDefaultMeta(): void
    {
        ConfigureCasinoSeoFiltersFixtures::build('https://lottopark.loc/');
        $whitelabel = new Whitelabel([
            'theme' => 'LottoPark'
        ]);
        $this->platform->configureCasinoSeoFiltersMocked($whitelabel);
    }
}
