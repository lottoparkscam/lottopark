<?php

namespace feature\classes\Repositories;

use Container;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Tests\Fixtures\WhitelabelFixture;

class WhitelabelRepositoryTest extends \Test_Feature
{
    private WhitelabelRepository $whitelabelRepositoryUnderTest;
    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepositoryUnderTest = Container::get(WhitelabelRepository::class);
    }

    /** @test */
    public function getAllActiveWhitelabels_noActiveWhitelabels_shouldReturnEmptyArray(): void
    {
        $lottoparkWhitelabel = $this->whitelabelRepositoryUnderTest->findOneByTheme(Whitelabel::LOTTOPARK_THEME);
        $lottoparkWhitelabel->isActive = false;
        $lottoparkWhitelabel->save();
        $this->assertSame([], $this->whitelabelRepositoryUnderTest->getAllActiveWhitelabels());
        $lottoparkWhitelabel->isActive = true;
        $lottoparkWhitelabel->save();
        $this->whitelabelRepositoryUnderTest->clearCache();
    }

    /** @test */
    public function getAllNotActiveWhitelabels_notActiveWhitelabelsNotExists_shouldReturnEmptyArray(): void
    {
        $this->assertSame([], $this->whitelabelRepositoryUnderTest->getAllNotActiveWhitelabels());
    }

    /** @test */
    public function getAllActiveWhitelabels_checkIfReturnsOnlyActiveWhitelabels(): void
    {
        $this->insertFakeLottoBazarWhitelabel();
        $allActiveWhitelabels = $this->whitelabelRepositoryUnderTest->getAllActiveWhitelabels();
        $this->assertNotEmpty($allActiveWhitelabels);
        /** @var Whitelabel $whitelabel */
        foreach ($allActiveWhitelabels as $whitelabel) {
            $this->assertTrue($whitelabel->isActive);
            $this->assertTrue($whitelabel->isTheme(Whitelabel::LOTTOPARK_THEME));
        }
        $allNotActiveWhitelabels = $this->whitelabelRepositoryUnderTest->getAllNotActiveWhitelabels();
        $this->assertNotEmpty($allNotActiveWhitelabels);
        foreach ($allNotActiveWhitelabels as $whitelabel) {
            $this->assertFalse($whitelabel->isActive);
            $this->assertTrue($whitelabel->isTheme(Whitelabel::LOTTOBAZAR_THEME));
        }
        $this->assertNotSame($allActiveWhitelabels, $allNotActiveWhitelabels);
    }

    private function insertFakeLottoBazarWhitelabel(): void
    {
        $whitelabel = $this->whitelabelRepositoryUnderTest->findOneByTheme(Whitelabel::LOTTOPARK_THEME);
        $whitelabelFixture = Container::get(WhitelabelFixture::class);
        $newWhitelabel = new Whitelabel($whitelabel->to_array());
        $newWhitelabel->set([
            'prefix'    => $whitelabelFixture->randomPrefix(),
            'theme'     => Whitelabel::LOTTOBAZAR_THEME,
            'is_active' => false
        ]);
        $newWhitelabel->save();
        $this->whitelabelRepositoryUnderTest->clearCache();
    }
}
