<?php

namespace Feature\Helpers\Wordpress;

use Models\Whitelabel;
use Models\WhitelabelLanguage;
use Repositories\WhitelabelLanguageRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelFixture;

class WhitelabelLanguageRepositoryTest extends Test_Feature
{
    public WhitelabelLanguageRepository $whitelabelLanguageRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelLanguageRepository = $this->container->get(WhitelabelLanguageRepository::class);
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
    }

    /** @test */
    public function getAll_whenWhitelabelNotExists(): void
    {
        $this->container->set('whitelabel', null);
        $actual = $this->whitelabelLanguageRepository->getAll();
        $this->assertEmpty($actual);
    }

    /** @test */
    public function getAll_whenWhitelabelExists(): void
    {
        $actual = $this->whitelabelLanguageRepository->getAll();
        $this->assertCount(2, $actual);
        $this->assertSame('en_GB', $actual[0]['code']);
        $this->assertSame('1', $actual[0]['wl_lang_id']);
        $this->assertSame('2', $actual[0]['currency_id']);
        $this->assertSame('2', $actual[0]['default_currency_id']);
        $this->assertSame('{c}{n}.{s}', $actual[0]['js_currency_format']);
        $this->assertSame('pl_PL', $actual[1]['code']);
        $this->assertSame('2', $actual[1]['wl_lang_id']);
        $this->assertSame('2', $actual[1]['currency_id']);
        $this->assertSame('2', $actual[1]['default_currency_id']);
        $this->assertSame('{n},{s} {c}', $actual[1]['js_currency_format']);
    }

    /** @test */
    public function getAll_whenWhitelabelInArguments(): void
    {
        // Given
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->whitelabelFixture->createOne();

        (new WhitelabelLanguage([
            'whitelabel_id' => $whitelabel->id,
            'language_id' => 3,
            'currency_id' => 1,
        ]))->save();
        (new WhitelabelLanguage([
            'whitelabel_id' => $whitelabel->id,
            'language_id' => 19,
            'currency_id' => 1,
        ]))->save();

        // When
        $actual = $this->whitelabelLanguageRepository->getAll($whitelabel);

        // Then
        $this->assertCount(2, $actual);
        $this->assertSame('de_DE', $actual[0]['code']);
        $this->assertSame('de_DE.utf8', $actual[0]['full_code']);
        $this->assertSame('sr_RS', $actual[1]['code']);
        $this->assertSame('sr_RS@latin', $actual[1]['full_code']);
    }
}
