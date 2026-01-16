<?php

namespace Tests\Feature\Classes\Repositories\Orm;

use Container;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Test_Feature;

class WhitelabelTest extends Test_Feature
{
    private WhitelabelRepository $whitelabelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
    }

    /** @test */
    public function getWhitelabelFromUrl_shouldReturnCorrectDomain()
    {
        $domain = 'lottopark.loc';
        $_SERVER['HTTP_HOST'] = $domain;
        $whitelabel = $this->whitelabelRepository->getWhitelabelFromUrl();

        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
        $this->assertSame($domain, $whitelabel->domain);

        $domain = 'www.lottopark.loc';
        $_SERVER['HTTP_HOST'] = $domain;
        $whitelabel = $this->whitelabelRepository->getWhitelabelFromUrl();

        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
        $this->assertSame('lottopark.loc', $whitelabel->domain);
    }
}
