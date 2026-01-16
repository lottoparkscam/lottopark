<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use Repositories\SocialTypeRepository;
use Repositories\WhitelabelUserSocialRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelSocialApiFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Tests\Fixtures\WhitelabelUserSocialFixture;

class WhitelabelUserSocialRepositoryTest extends Test_Feature
{
    private WhitelabelUserSocialRepository $whitelabelUserSocialRepositoryUnderTest;
    private WhitelabelUserSocialFixture $whitelabelUserSocialFixture;
    private WhitelabelSocialApiFixture $whitelabelSocialApiFixture;
    private SocialTypeRepository $socialTypeRepository;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelUserSocialRepositoryUnderTest = $this->container->get(WhitelabelUserSocialRepository::class);
        $this->whitelabelUserSocialFixture = $this->container->get(WhitelabelUserSocialFixture::class);
        $this->whitelabelSocialApiFixture = $this->container->get(WhitelabelSocialApiFixture::class);
        $this->socialTypeRepository = $this->container->get(SocialTypeRepository::class);
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
    }

    /** @test  */
    public function findOneByWhitelabelUserIdAndWhitelabelSocialAppId(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertSame($result->id, $whitelabelUserSocial->id);
    }

    /** @test  */
    public function findOneByWhitelabelUserIdAndWhitelabelSocialAppId_whitelabelUserSocialNotExists(): void
    {
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId(1123, 9992);
        $this->assertNull($result);
    }

    /** @test  */
    public function findOneByWhitelabelUserIdAndWhitelabelSocialAppId_whitelabelUserIsDeleted(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations([], true);
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertNull($result);
    }

    /** @test  */
    public function findOneByWhitelabelUserIdAndWhitelabelSocialAppId_whitelabelUserIsDeleted_userCreatedTheSameAccount(): void
    {
        $email = 'test@user.loc';
        $whitelabelUserSocialDeletedAccount = $this->createWhitelabelUserSocialWithRelations([], true, $email);
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations([], false, $email);

        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId(
            $whitelabelUserSocial->whitelabelUserId,
            $whitelabelUserSocial->whitelabelSocialApiId
        );

        $this->assertEquals($whitelabelUserSocial->whitelabelUser->id, $result->whitelabelUserId);
        $this->assertNotEquals($whitelabelUserSocialDeletedAccount->whitelabelUser->id, $result->whitelabelUserId);
    }

    /** @test  */
    public function findOneByWhitelabelUserIdAndWhitelabelSocialAppId_whitelabelUserIsNotDeleted(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertSame($whitelabelUserSocial->whitelabelUser->id, $result->whitelabelUser->id);
    }

    /** @test  */
    public function findOneByWhitelabelUserSocialIdAndWhitelabelSocialAppId(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByUserSocialIdAndWhitelabelSocialAppId($whitelabelUserSocial->socialUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertSame($result->id, $whitelabelUserSocial->id);
    }

    /** @test  */
    public function findOneByWhitelabelUserSocialIdAndWhitelabelSocialAppId_whitelabelUserSocialNotExists(): void
    {
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByUserSocialIdAndWhitelabelSocialAppId(1123, 9992);
        $this->assertNull($result);
    }

    /** @test  */
    public function findOneByUserSocialIdAndWhitelabelSocialAppId_whitelabelUserIsDeleted(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations([], true);
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByUserSocialIdAndWhitelabelSocialAppId($whitelabelUserSocial->socialUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertNull($result);
    }

    /** @test  */
    public function findOneByUserSocialIdAndWhitelabelSocialAppId_whitelabelUserIsDeleted_userCreatedTheSameAccount(): void
    {
        $email = 'test@user.loc';
        $whitelabelUserSocialDeletedAccount = $this->createWhitelabelUserSocialWithRelations([], true, $email);
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations([], false, $email);

        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByUserSocialIdAndWhitelabelSocialAppId(
            $whitelabelUserSocial->socialUserId,
            $whitelabelUserSocial->whitelabelSocialApiId
        );

        $this->assertEquals($whitelabelUserSocial->whitelabelUser->id, $result->whitelabelUserId);
        $this->assertNotEquals($whitelabelUserSocialDeletedAccount->whitelabelUser->id, $result->whitelabelUserId);
    }

    /** @test  */
    public function findOneByWhitelabelUserSocialIdAndWhitelabelSocialAppId_whitelabelUserIsNotDeleted(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $result = $this->whitelabelUserSocialRepositoryUnderTest->findEnabledByUserSocialIdAndWhitelabelSocialAppId($whitelabelUserSocial->socialUserId, $whitelabelUserSocial->whitelabelSocialApiId);
        $this->assertSame($whitelabelUserSocial->whitelabelUser->id, $result->whitelabelUser->id);
    }

    /** @test */
    public function updateHashAndHashDate(): void
    {
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';

        $this->whitelabelUserSocialRepositoryUnderTest->updateHash($whitelabelUserSocial->id, $newHash, $dateHashSent);
        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOne();
        $whitelabelUserSocialAfterUpdate->reload();

        $this->assertSame($whitelabelUserSocialAfterUpdate->activationHash, $newHash);
        $this->assertEquals($whitelabelUserSocialAfterUpdate->lastHashSentAt, new Carbon($dateHashSent));
    }

    /** @test */
    public function updateHashAndHashDate_onlyOneUserCanBeUpdated(): void
    {
        $additionalParametersForUserSocial = [
            'activation_hash' => null,
            'last_hash_sent_at' => null,
        ];
        $firstWhitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations();
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';

        $this->whitelabelUserSocialRepositoryUnderTest->updateHash($whitelabelUserSocial->id, $newHash, $dateHashSent);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        /** @var WhitelabelUserSocial $firstWhitelabelUserSocialAfterUpdate */
        $firstWhitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($firstWhitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();
        $firstWhitelabelUserSocialAfterUpdate->reload();

        $this->assertNull($firstWhitelabelUserSocialAfterUpdate->activationHash);
        $this->assertNull($firstWhitelabelUserSocialAfterUpdate->lastHashSentAt);
        $this->assertSame($whitelabelUserSocialAfterUpdate->activationHash, $newHash);
        $this->assertEquals($whitelabelUserSocialAfterUpdate->lastHashSentAt, new Carbon($dateHashSent));
    }

    /** @test */
    public function removeUnusedHashAndHashSentDate_userIsNotConfirmed_hashCanNotBeChanged(): void
    {
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';
        $additionalParametersForUserSocial = [
            'is_confirmed' => false,
            'activation_hash' => $newHash,
            'last_hash_sent_at' => $dateHashSent,
        ];
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);

        $this->whitelabelUserSocialRepositoryUnderTest->updateHash($whitelabelUserSocial->id, $newHash, $dateHashSent);
        $this->whitelabelUserSocialRepositoryUnderTest->removeUnusedHashAndHashSentDate($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();

        $this->assertFalse($whitelabelUserSocial->isConfirmed);
        $this->assertSame($newHash, $whitelabelUserSocialAfterUpdate->activationHash);
        $this->assertEquals(new Carbon($dateHashSent), $whitelabelUserSocialAfterUpdate->lastHashSentAt);
    }

    /** @test */
    public function removeUnusedHashAndHashSentDate(): void
    {
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';
        $additionalParametersForUserSocial = [
            'is_confirmed' => true,
            'activation_hash' => $newHash,
            'last_hash_sent_at' => $dateHashSent,
        ];
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);

        $this->whitelabelUserSocialRepositoryUnderTest->removeUnusedHashAndHashSentDate($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();

        $this->assertTrue($whitelabelUserSocial->isConfirmed);
        $this->assertNull($whitelabelUserSocialAfterUpdate->activationHash);
        $this->assertNull($whitelabelUserSocialAfterUpdate->lastHashSentAt);
    }

    /** @test */
    public function removeUnusedHashAndHashSentDate_onlyOneUserCanBeUpdated(): void
    {
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';
        $additionalParametersForFirstUserSocial = [
            'is_confirmed' => false,
            'activation_hash' => $newHash,
            'last_hash_sent_at' => $dateHashSent,
        ];
        $firstWhitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForFirstUserSocial);

        $additionalParametersForUserSocial = [
            'is_confirmed' => true,
            'activation_hash' => null,
            'last_hash_sent_at' => null,
        ];
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);

        $this->whitelabelUserSocialRepositoryUnderTest->removeUnusedHashAndHashSentDate($whitelabelUserSocial->whitelabelUserId, $whitelabelUserSocial->whitelabelSocialApiId);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        /** @var WhitelabelUserSocial $firstWhitelabelUserSocialAfterUpdate */
        $firstWhitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($firstWhitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();
        $firstWhitelabelUserSocialAfterUpdate->reload();
        $this->assertTrue($whitelabelUserSocial->isConfirmed);
        $this->assertNull($whitelabelUserSocial->activationHash);
        $this->assertNull($whitelabelUserSocial->lastHashSentAt);

        $this->assertFalse($firstWhitelabelUserSocialAfterUpdate->isConfirmed);
        $this->assertSame($firstWhitelabelUserSocialAfterUpdate->activationHash, $newHash);
        $this->assertEquals($firstWhitelabelUserSocialAfterUpdate->lastHashSentAt, new Carbon($dateHashSent));
    }

    /** @test */
    public function confirmSocialLogin(): void
    {
        $additionalParametersForUserSocial = [
            'is_confirmed' => false
        ];
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);

        $this->whitelabelUserSocialRepositoryUnderTest->confirmSocialLogin($whitelabelUserSocial->id, $whitelabelUserSocial->whitelabelSocialApiId);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();

        $this->assertTrue($whitelabelUserSocialAfterUpdate->isConfirmed);
    }

    /** @test */
    public function confirmSocialLogin_onlyOneUserCanBeUpdated(): void
    {
        $additionalParametersForUserSocial = [
            'is_confirmed' => false
        ];
        $firstWhitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);
        $whitelabelUserSocial = $this->createWhitelabelUserSocialWithRelations($additionalParametersForUserSocial);

        $this->whitelabelUserSocialRepositoryUnderTest->confirmSocialLogin($whitelabelUserSocial->id, $whitelabelUserSocial->whitelabelSocialApiId);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterUpdate */
        $whitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocial->id);
        /** @var WhitelabelUserSocial $firstWhitelabelUserSocialAfterUpdate */
        $firstWhitelabelUserSocialAfterUpdate = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($firstWhitelabelUserSocial->id);
        $whitelabelUserSocialAfterUpdate->reload();
        $firstWhitelabelUserSocialAfterUpdate->reload();

        $this->assertTrue($whitelabelUserSocialAfterUpdate->isConfirmed);
        $this->assertFalse($firstWhitelabelUserSocialAfterUpdate->isConfirmed);
    }

    /** @test */
    public function insert(): void
    {
        $socialUserId = 'asdq213asd123as123';
        $newHash = 'asdqweqdsaqwdas';
        $dateHashSent = '2023-02-15T22:24:31.00';
        /** @var SocialType $socialType */
        $socialType = $this->socialTypeRepository->findOne();
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->whitelabelFixture->createOne();
        /** @var WhitelabelSocialApi $whitelabelSocialApi */
        $whitelabelSocialApi = $this->whitelabelSocialApiFixture->withSocialType($socialType)->withWhitelabel($whitelabel)->createOne();
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserFixture->createOne([
            'whitelabel_id' => $whitelabel->id,
            'currency_id' => 2,
            'email' => 'testSocial@Mediatestowo.pl' . random_int(1, 3),
        ]);
        $credentials = [
            'whitelabelUserId' => $whitelabelUser->id,
            'whitelabelSocialApiId' => $whitelabelSocialApi->id,
            'socialUserId' => $socialUserId,
            'isConfirmed' => false,
            'activationHash' => $newHash,
            'lastHashSentAt' => $dateHashSent,
        ];

        $whitelabelUserSocialAfterInsert = $this->whitelabelUserSocialRepositoryUnderTest->insert($credentials);

        /** @var WhitelabelUserSocial $whitelabelUserSocialAfterInsert */
        $whitelabelUserSocialAfterInsert = $this->whitelabelUserSocialRepositoryUnderTest->findOneById($whitelabelUserSocialAfterInsert->id);
        $whitelabelUserSocialAfterInsert->reload();
        $this->assertNotNull($whitelabelUserSocialAfterInsert->id);
        $this->assertFalse($whitelabelUserSocialAfterInsert->isConfirmed);
        $this->assertSame($whitelabelUser->id, $whitelabelUserSocialAfterInsert->whitelabelUserId);
        $this->assertSame($whitelabelSocialApi->id, $whitelabelUserSocialAfterInsert->whitelabelSocialApiId);
        $this->assertSame($socialUserId, $whitelabelUserSocialAfterInsert->socialUserId);
        $this->assertSame($newHash, $whitelabelUserSocialAfterInsert->activationHash);
        $this->assertEquals(new Carbon($dateHashSent), $whitelabelUserSocialAfterInsert->lastHashSentAt);
    }

    private function createWhitelabelUserSocialWithRelations(
        array $additionalParameters = [],
        bool $userIsDeleted = false,
        string $userEmail = '',
    ): WhitelabelUserSocial {
        $socialType = $this->socialTypeRepository->findOne();
        /** @var Whitelabel $whitelabel */
        $whitelabel = $this->whitelabelFixture->createOne();
        $whitRandomEmail = empty($userEmail);
        $email = $whitRandomEmail ? 'testSocial@Mediatestowo.pl' . random_int(1, 3) : $userEmail;
        $whitelabelUser = $this->whitelabelUserFixture
            ->createOne([
                'whitelabel_id' => $whitelabel->id,
                'currency_id' => 2,
                'email' => $email,
                'is_deleted' => $userIsDeleted,
            ]);
        $whitelabelSocialApi = $this->whitelabelSocialApiFixture->withSocialType($socialType)->withWhitelabel($whitelabel)->createOne();
        /** @var WhitelabelUserSocial $whitelabelUserSocial */
        return $this->whitelabelUserSocialFixture
            ->withWhitelabelSocialApiId($whitelabelSocialApi)
            ->withWhitelabelUser($whitelabelUser)
            ->createOne($additionalParameters);
    }
}
