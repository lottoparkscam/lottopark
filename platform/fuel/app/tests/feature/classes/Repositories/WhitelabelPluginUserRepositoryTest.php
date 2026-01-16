<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Repositories;

use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelPluginUserRepository;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Tests\Fixtures\WhitelabelPluginFixture;
use Tests\Fixtures\WhitelabelPluginUserFixture;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Container;
use Test_Feature;

final class WhitelabelPluginUserRepositoryTest extends Test_Feature
{
    private Whitelabel $whitelabel;
    private WhitelabelPlugin $whitelabelPlugin;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelPluginUserRepository $whitelabelPluginUserRepository;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelPluginFixture $whitelabelPluginFixture;
    private WhitelabelPluginUserFixture $whitelabelPluginUserFixture;

    private array $newUsers = [];

    private array $testUsers = [
        'test1@user.loc',
        'test2@user.loc',
        'test3@user.loc',
        'test4@user.loc',
        'test5@user.loc'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->whitelabelPluginUserRepository = Container::get(WhitelabelPluginUserRepository::class);
        $this->whitelabelUserFixture =  Container::get(WhitelabelUserFixture::class);
        $this->whitelabelFixture =  Container::get(WhitelabelFixture::class);
        $this->whitelabelPluginFixture =  Container::get(WhitelabelPluginFixture::class);
        $this->whitelabelPluginUserFixture =  Container::get(WhitelabelPluginUserFixture::class);

        $this->whitelabel = $this->whitelabelFixture->createOne();

        $this->whitelabelPlugin = $this->whitelabelPluginFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'plugin' => 'mautic-api'
            ]);

        foreach ($this->testUsers as $testUserEmail) {
            $this->newUsers[] = $this->whitelabelUserFixture
                ->with(WhitelabelUserFixture::BASIC)
                ->createOne([
                    'whitelabel_id' => $this->whitelabel->id,
                    'currency_id' => 2,
                    'email' => $testUserEmail
                ]);
        }
    }

    public function getActiveUsersDataProvider(): array
    {
        return [
            'no users added - no matching filters criteria' => [5, 0, [], []],
            'all users inactive' => [0, 0, [], ['test1@user.loc', 'test2@user.loc', 'test3@user.loc', 'test4@user.loc', 'test5@user.loc']],
            'all users active' => [5, 5, ['test1@user.loc', 'test2@user.loc', 'test3@user.loc', 'test4@user.loc', 'test5@user.loc'], []],
            '3 users active' => [5, 3, ['test1@user.loc', 'test2@user.loc', 'test3@user.loc'], []],
            '3 users active and 3 inactive' => [3, 3, ['test1@user.loc', 'test2@user.loc', 'test3@user.loc'], ['test4@user.loc', 'test5@user.loc']],
        ];
    }

    /**
     * @test
     * @dataProvider getActiveUsersDataProvider
     */
    public function getActiveUsersCriterias_ShouldReturnOnlyActiveWhitelabelUsers(
        int $expectedUsersCount,
        int $expectedActivePluginUsersCount,
        array $activeUsers,
        array $inActiveUsers,
    ): void {

        $activeUsersCriterias = WhitelabelPluginUserRepository::getActiveUsersCriterias($this->whitelabelPlugin->id);

        foreach ($this->newUsers as $user) {
            $newPluginUser = $this->whitelabelPluginUserFixture
                ->withWhitelabelUser($user)
                ->withWhitelabelPlugin($this->whitelabelPlugin);

            if (in_array($user->email, $activeUsers)) {
                $newPluginUser->createOne(['is_active' => true]);
            }

            if (in_array($user->email, $inActiveUsers)) {
                $newPluginUser->createOne(['is_active' => false]);
            }
        }

        $this->whitelabelPluginUserRepository->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_plugin_id', $this->whitelabelPlugin->id),
            new Model_Orm_Criteria_Where('is_active', true)
        ]);

        $actualPluginUsers = $this->whitelabelPluginUserRepository->getResults();

        $this->whitelabelUserRepository->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $this->whitelabel->id),
        ]);

        $this->whitelabelUserRepository->pushCriterias($activeUsersCriterias);

        $actualUsers = $this->whitelabelUserRepository->getResults();

        $this->assertCount($expectedActivePluginUsersCount, $actualPluginUsers);
        $this->assertCount($expectedUsersCount, $actualUsers);
    }
}
