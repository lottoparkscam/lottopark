<?php

namespace Feature\Classes\Models;

use Container;
use Factories\WhitelabelUserBalanceLog;
use Factory_Orm_Whitelabel_Lottery;
use Factory_Orm_Whitelabel_User;
use Fuel\Core\Cache;
use Fuel\Core\DB;
use Classes\Orm\AbstractOrmModel;
use Models\Lottery;
use Models\Whitelabel;
use Models\WhitelabelLottery;
// as because Factories\ has the same class name
use Models\WhitelabelUserBalanceLog as WhitelabelUserBalanceLogModel;
use Models\WhitelabelUser;
use Repositories\Orm\WhitelabelRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Wrappers\Orm;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\OrmModelInterface;

class ModelAbstractTest extends Test_Feature
{
    private array $modelsToDelete = [];
    private WhitelabelUserFixture $userFixture;
    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelUserRepository $whitelabelUserRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->modelsToDelete = array_reverse($this->modelsToDelete);

        /** @var AbstractOrmModel $model */
        foreach ($this->modelsToDelete as $model) {
            $model->delete();
        }
    }

    /**
     * @test
     * @group skipped
     */
    public function relation__with_cache__single_query()
    {
        $prevCount = DB::$query_count;
        Cache::delete_all();
        Whitelabel::flush_cache();
        Orm::enableCaching();

        $newDomain = 'newdomaintotest';
        $whitelabelLotteries = WhitelabelLottery::find('all');
        /** @var WhitelabelLottery $firstWhitelabelLottery */
        $firstWhitelabelLottery = current($whitelabelLotteries);
        /** @var WhitelabelLottery $secondWhitelabelLottery */
        $secondWhitelabelLottery = next($whitelabelLotteries);
        $previousGotWhitelabel = $secondWhitelabelLottery->whitelabel;

        $this->assertSame($firstWhitelabelLottery->whitelabel->id, $secondWhitelabelLottery->whitelabel->id);

        $firstWhitelabelLottery->whitelabel->set(['domain' => $newDomain]);

        $this->assertSame($newDomain, $secondWhitelabelLottery->whitelabel->domain);
        $this->assertSame($newDomain, $previousGotWhitelabel->domain);
        $this->assertSame(2, DB::$query_count - $prevCount);
    }

    /**
     * @test
     * @group skipped
     */
    public function relation__without_cache__multiple_query()
    {
        $prevCount = DB::$query_count;
        Cache::delete_all();
        Whitelabel::flush_cache();
        Orm::disableCaching();

        $newDomain = 'newdomaintotest';
        $whitelabelLotteries = WhitelabelLottery::find('all');
        /** @var WhitelabelLottery $firstWhitelabelLottery */
        $firstWhitelabelLottery = current($whitelabelLotteries);
        /** @var WhitelabelLottery $secondWhitelabelLottery */
        $secondWhitelabelLottery = next($whitelabelLotteries);
        $previousGotWhitelabel = $secondWhitelabelLottery->whitelabel;

        $this->assertSame($firstWhitelabelLottery->whitelabel->id, $secondWhitelabelLottery->whitelabel->id);

        $firstWhitelabelLottery->whitelabel->set(['domain' => $newDomain]);

        $this->assertNotSame($newDomain, $secondWhitelabelLottery->whitelabel->domain);
        $this->assertNotSame($newDomain, $previousGotWhitelabel->domain);
        $this->assertSame(3, DB::$query_count - $prevCount);
    }

    /**
     * @test
     * @group skipped
     */
    public function relation__with_cache_but_with_cache_in_relation_disabled__multiple_query()
    {
        $prevCount = DB::$query_count;
        Cache::delete_all();
        Whitelabel::flush_cache();
        WhitelabelUser::flush_cache();
        Orm::enableCaching();
        $queryCountOffset = 0;

        if (empty($user = WhitelabelUser::find(1))) {
            $userFactory = Factory_Orm_Whitelabel_User::forge(['login' => 'newlogin']);
            $user = $userFactory->build();
            ++$queryCountOffset;
            $this->modelsToDelete[] = $user;
        }

        $data = ['whitelabel_user_id' => $user->id];
        $factoryWhitelabelUserBalanceLog = new WhitelabelUserBalanceLog($data);
        $this->modelsToDelete[] = $factoryWhitelabelUserBalanceLog->build();
        $this->modelsToDelete[] = $factoryWhitelabelUserBalanceLog->build();

        $logs = WhitelabelUserBalanceLogModel::find('all', [
            'where' => [
                'whitelabel_user_id' => $user->id
            ]
        ]);
        /** @var WhitelabelUserBalanceLogModel $log1 */
        $log1 = current($logs);
        /** @var WhitelabelUserBalanceLogModel $log2 */
        $log2 = next($logs);

        $log1->whitelabel_user;
        $log2->whitelabel_user;

        $this->assertSame(6 + $queryCountOffset, DB::$query_count - $prevCount);
    }

    /** @test */
    public function relation__in_static_method()
    {
        /** @var Lottery $lottery */
        $lottery = Lottery::find('first');


        $whitelabelLottery = WhitelabelLottery::find('first', [
            'where' => [
                'lottery_id' => $lottery->id
            ]
        ]);

        if (empty($whitelabelLottery)) {
            $factory = new Factory_Orm_Whitelabel_Lottery([
                'lottery_id' => $lottery->id,
                'whitelabel_id' => 1,
                'is_enabled' => 1
            ]);
            $whitelabelLottery = $factory->build();
            $this->modelsToDelete[] = $whitelabelLottery;
        }

        $exampleModel = WhitelabelLottery::find('first', [
            'related' => [
                'lottery' => [
                    'where' => [
                        'is_enabled' => true
                    ]
                ]
            ]
        ]);

        $this->assertIsObject($exampleModel->lottery);
        $this->assertEquals($lottery, $exampleModel->lottery);
    }

    /**
     * @test
     */
    public function updateFloatField_afterUpdateSingleField_shouldReturnCorrectValue()
    {
        $addValue = 20;
        $fieldName = 'bonus_balance';

        /** @var $user WhitelabelUser  */
        $user = $this->userFixture->with('basic')->createOne();

        $this->whitelabelUserRepository->updateFloatField(
            $user->id,
            $fieldName,
            $addValue
        );

        $userAfterUpdate = $this->whitelabelUserRepository->getById($user->id);

        $this->assertSame($user->$fieldName + $addValue, $userAfterUpdate->$fieldName);
    }

    /**
     * @test
     */
    public function updateFloatField_afterUpdateMultipleFields_shouldReturnCorrectValues()
    {
        $addValue = 20.00;
        $fieldsNames = ['pnl_manager', 'total_net_income_manager'];

        /** @var $user WhitelabelUser  */
        $user = $this->userFixture->with('basic')->createOne();

        $this->whitelabelUserRepository->updateFloatField(
            $user->id,
            $fieldsNames,
            $addValue
        );

        $userAfterUpdate = $this->whitelabelUserRepository->getById($user->id);

        $this->assertSame($user->pnl_manager + $addValue, $userAfterUpdate->pnl_manager);
        $this->assertSame($user->total_net_income_manager + $addValue, $userAfterUpdate->total_net_income_manager);
    }

    /**
     * @test
     */
    public function updateFloatField_WithValue0_shouldThrowException()
    {
        $fieldName = 'margin';

        $this->expectExceptionMessage("Cannot update {$fieldName} if newValue is 0");

        /** @var $user WhitelabelUser  */
        $user = $this->userFixture->with('basic')->createOne();

        $this->whitelabelUserRepository->updateFloatField(
            $user->id,
            $fieldName,
            0
        );
    }

    /**
     * @group skipped
     * @test
     */
    public function updateFloatField_afterUpdate_shouldReloadCache()
    {
        $this->markTestIncomplete('updatefloadField needs to be changed');
        Cache::delete_all();
        Whitelabel::flush_cache();
        Orm::enableCaching();

        /** @var Whitelabel $whitelabel */
        $whitelabel = Whitelabel::find(1);
        $previousAmount = $whitelabel->margin;

        $this->whitelabelRepository->updateFloatField(
            $whitelabel->id,
            'margin',
            20
        );

        $this->assertSame($previousAmount + 20, $whitelabel->margin);
    }

    /** @test */
    public function getCount_ManyCalls_ShouldNotAggregateCriteria(): void
    {
        // Given an "Active records" model mapped to raffle table
        $model = $this->fakeModel();
        $countBeforeSeed = $model->getCount();

        // And DB with Raffles to a/m table
        $fixture = $this->container->get(RaffleFixture::class);
        $fixture->with('basic')->createOne(['name' => 'raffle1']);
        $fixture->with('basic')->createOne(['name' => 'raffle2']);

        // And criteria are pushed two times with different payload
        $model->push_criteria(new Model_Orm_Criteria_Where('name', 'raffle1'));

        // When getCount is called with 3rd argument as true (should reset query)
        $result1 = $model->getCount(null, null, true);
        $result2 = $model->getCount(null, null, true);

        // Then each get count should have its own results (criteria should be cleaned up after each call)
        $this->assertSame($countBeforeSeed + 1, $result1);
        $this->assertSame($countBeforeSeed + 2, $result2);
    }

    /** @test */
    public function getCount_ManyCalls_ShouldAggregateCriteria(): void
    {
        // Given an "Active records" model mapped to raffle table
        $model = $this->fakeModel();
        $countBeforeSeed = $model->getCount();

        // And DB with Raffles to a/m table
        $fixture = $this->container->get(RaffleFixture::class);
        $fixture->with('basic')->createOne(['name' => 'raffle1']);
        $fixture->with('basic')->createOne(['name' => 'raffle2']);

        // And criteria are pushed two times with different payload
        $model->push_criteria(new Model_Orm_Criteria_Where('name', 'raffle1'));

        // When getCount is called without 3rd argument (should not reset query)
        $result1 = $model->getCount();
        $result2 = $model->getCount();

        // Then each get count should have the same results (criteria should not be cleaned up after each call)
        $this->assertSame($countBeforeSeed + 1, $result1);
        $this->assertSame($countBeforeSeed + 1, $result2);
    }

    public function fakeModel(): OrmModelInterface
    {
        return new class ($data = [], $new = true, $view = null, $cache = true) extends AbstractOrmModel {
            protected static $_table_name = 'raffle';

            protected static $_properties = [
                'id',
                'name',
            ];
        };
    }
}
