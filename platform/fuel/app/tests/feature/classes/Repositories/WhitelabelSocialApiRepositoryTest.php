<?php

namespace Tests\Feature\Classes\Repositories;

use Models\SocialType;
use Models\WhitelabelSocialApi;
use Repositories\WhitelabelSocialApiRepository;
use Test_Feature;

class WhitelabelSocialApiRepositoryTest extends Test_Feature
{
    private WhitelabelSocialApiRepository $whitelabelSocialApiRepositoryUnderTest;
    private WhitelabelSocialApi $whitelabelSocialApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelSocialApiRepositoryUnderTest = $this->container->get(WhitelabelSocialApiRepository::class);
        $this->whitelabelSocialApi = $this->whitelabelSocialApiRepositoryUnderTest->findOne();
    }

    /** @test */
    public function findWhitelabelSocialSettingsBySocialType(): void
    {
        /** @var WhitelabelSocialApi $whitelabelSocialApiUnderTest */
        $whitelabelSocialApiUnderTest = $this->whitelabelSocialApiRepositoryUnderTest->findWhitelabelSocialSettingsBySocialType(SocialType::FACEBOOK_TYPE);
        $whitelabelSocialApiUnderTest->reload();
        $this->assertSame($this->whitelabelSocialApi->id, $whitelabelSocialApiUnderTest->id);
    }

    /** @test */
    public function findWhitelabelSocialSettingsBySocialType_settingsNotExists_returnNull(): void
    {
        $this->whitelabelSocialApi->delete();
        /** @var WhitelabelSocialApi $whitelabelSocialApiUnderTest */
        $whitelabelSocialApiUnderTest = $this->whitelabelSocialApiRepositoryUnderTest->findWhitelabelSocialSettingsBySocialType(SocialType::FACEBOOK_TYPE);
        $this->assertNull($whitelabelSocialApiUnderTest);
    }
}
