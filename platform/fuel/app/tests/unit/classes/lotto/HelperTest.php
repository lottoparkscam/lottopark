<?php

namespace Tests\Unit\Classes\Lotto;

use Lotto_Helper;
use Test_Unit;

class HelperTest extends Test_Unit
{
    /** @test */
    public function getWordpressFileUrlPath(): void
    {
        $whitelabel = $this->container->get('whitelabel');
        $whitelabel['domain'] = 'test.domain';
        $whitelabel['theme'] = 'lottotheme';
        $fileName = 'file.php';

        ['url' => $url, 'path' => $path] = Lotto_Helper::get_wordpress_file_url_path($whitelabel->to_array(), $fileName);
        $root = substr(DOCROOT, 0, strpos(DOCROOT, 'platform'));

        $this->assertSame('https://test.domain/wp-content/themes/lottotheme/file.php', $url);
        $this->assertSame($root . 'wordpress/wp-content/themes/lottotheme/file.php', $path);
    }
}
