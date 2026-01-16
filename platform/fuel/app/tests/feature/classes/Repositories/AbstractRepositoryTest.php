<?php

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\Whitelabel;
use Repositories\WhitelabelLotteryRepository;
use Repositories\WhitelabelRepository;
use Tests\Fixtures\WhitelabelFixture;

class AbstractRepositoryTest extends Test_Feature
{
    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private WhitelabelFixture $whitelabelFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->whitelabelLotteryRepository = Container::get(WhitelabelLotteryRepository::class);
        $this->whitelabelFixture = Container::get(WhitelabelFixture::class);
    }

    /** @test */
    public function findByColumn(): void
    {
        $whitelabels = $this->whitelabelRepository->findByDomain('lottopark.loc', '=');
        $this->assertInstanceOf(Whitelabel::class, $whitelabels[0]);
        $this->assertSame('lottopark.loc', $whitelabels[0]->domain);
    }

    /** @test */
    public function findByColumnWithLongName(): void
    {
        $whitelabels = $this->whitelabelRepository->findByWelcomePopupTimeout(30);
        $this->assertInstanceOf(Whitelabel::class, $whitelabels[0]);
        $this->assertSame('lottopark.loc', $whitelabels[0]->domain);
    }

    /** @test */
    public function findOneByColumn(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByDomain('lottopark.loc', '=');
        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
        $this->assertSame('lottopark.loc', $whitelabel->domain);
    }

    /** @test */
    public function findOneByColumnWithLongName(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByWelcomePopupTimeout(30);
        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
        $this->assertSame('lottopark.loc', $whitelabel->domain);
    }

    /** @test */
    public function recordExistsShouldReturnValid(): void
    {
        $actual = $this->whitelabelRepository->recordExists([
            new Model_Orm_Criteria_Where('name', 'lottopark')
        ]);

        $this->assertTrue($actual);
    }

    /** @test */
    public function recordNotExistsShouldReturnValid(): void
    {
        $actual = $this->whitelabelRepository->recordNotExists([
            new Model_Orm_Criteria_Where('name', 'asdasdxasdxasd')
        ]);

        $this->assertTrue($actual);
    }

    /** @test */
    public function getIntCountShouldReturnValid(): void
    {
        $actual = $this->whitelabelRepository->getIntCount([
            new Model_Orm_Criteria_Where('name', 'lottopark')
        ]);

        $this->assertEquals(1, $actual);
    }

    /** @test */
    public function getIntCountShouldReturnMultipleRecordsCount(): void
    {
        $whitelabel = $this->container->get('whitelabel');
        $whitelabel = $whitelabel->to_array();

        $newWhitelabel = new Whitelabel($whitelabel);
        $newWhitelabel->set(['prefix' => $this->whitelabelFixture->randomPrefix()]);
        $newWhitelabel->save();

        $newWhitelabel = new Whitelabel($whitelabel);
        $newWhitelabel->set(['prefix' => $this->whitelabelFixture->randomPrefix()]);
        $newWhitelabel->save();

        $newWhitelabel = new Whitelabel($whitelabel);
        $newWhitelabel->set(['prefix' => $this->whitelabelFixture->randomPrefix()]);
        $newWhitelabel->save();

        $actual = $this->whitelabelRepository->getIntCount([
            new Model_Orm_Criteria_Where('name', 'lottopark')
        ]);

        $this->assertEquals(4, $actual);
    }

    /** @test */
    public function getIntCountRecordNotExistsShouldReturn0(): void
    {
        $actual = $this->whitelabelRepository->getIntCount([
            new Model_Orm_Criteria_Where('name', 'xysfds')
        ]);

        $this->assertEquals(0, $actual);
    }

    /** @test */
    public function getRelations_NoRelationsAreLoaded(): void
    {
        $whitelabelLottery = $this->whitelabelLotteryRepository->getById(1);
        $lotteryRelation = $whitelabelLottery->_relate(false);
        $this->assertEmpty($lotteryRelation);
    }

    /** @test */
    public function getRelation_RelationIsEagerLoaded(): void
    {
        $whitelabelLottery = $this->whitelabelLotteryRepository->withRelation('lottery')->getById(1);
        $lotteryRelation = $whitelabelLottery->_relate(false);
        $this->assertNotEmpty($lotteryRelation);
        $this->assertEquals(count($lotteryRelation), 1);
        $this->assertNotEmpty($lotteryRelation['lottery']);
    }

    /** @test */
    public function getRelations_RelationIsEagerLoaded(): void
    {
        $whitelabelLottery = $this->whitelabelLotteryRepository->withRelations(['lottery'])->getById(1);
        $lotteryRelation = $whitelabelLottery->_relate(false);
        $this->assertNotEmpty($lotteryRelation);
        $this->assertEquals(count($lotteryRelation), 1);
        $this->assertNotEmpty($lotteryRelation['lottery']);
    }

    /** @test */
    public function getRelations_MultipleRelationsAreEagerLoaded(): void
    {
        $whitelabelLottery = $this->whitelabelLotteryRepository->withRelations(['lottery', 'whitelabel'])->getById(1);
        $lotteryRelations = $whitelabelLottery->_relate(false);
        $this->assertNotEmpty($lotteryRelations);
        $this->assertEquals(count($lotteryRelations), 2);
        $this->assertNotEmpty($lotteryRelations['lottery']);
        $this->assertNotEmpty($lotteryRelations['whitelabel']);
    }
}
