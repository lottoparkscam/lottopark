<?php

namespace Tests\Feature\Classes\Services\Aff;

use Models\{
    Whitelabel,
    WhitelabelAff,
    WhitelabelUser,
    WhitelabelUserAff,
};
use Repositories\{
    Aff\WhitelabelAffRepository,
    Aff\WhitelabelUserAffRepository
};
use Services\AffUserService;
use Forms_Whitelabel_Bonuses_Promocodes_Code;
use Helpers_General;
use Lotto_Security;
use Tests\Fixtures\{
    WhitelabelUserFixture,
    WhitelabelCampaignFixture
};
use PHPUnit\Framework\MockObject\MockObject;
use Exception;
use Test_Feature;

final class AffUserServiceTest extends Test_Feature
{
    private const NO_PROMO_CODE = 'no-promo-code';
    private const VALID_PROMO_CODE = 'valid-promo-code';
    private const INVALID_PROMO_CODE = 'invalid-promo-code';

    private Whitelabel $whitelabel;
    private WhitelabelUser $whitelabelUser;
    private WhitelabelAffRepository $whitelabelAffRepository;
    private WhitelabelUserAffRepository $whitelabelUserAffRepository;
    private AffUserService $affUserService;

    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelCampaignFixture $whitelabelCampaignFixture;
    private Forms_Whitelabel_Bonuses_Promocodes_Code|MockObject $formPromoCodeMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = $this->container->get('whitelabel');
        $this->whitelabelAffRepository = $this->container->get(WhitelabelAffRepository::class);
        $this->whitelabelUserAffRepository = $this->container->get(WhitelabelUserAffRepository::class);
        $this->affUserService = $this->container->get(AffUserService::class);

        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelCampaignFixture = $this->container->get(WhitelabelCampaignFixture::class);
        $this->whitelabelCampaignFixture->setPrefix('PROMO_CODE');
        $this->whitelabelCampaignFixture->withWhitelabel($this->whitelabel);

        $this->formPromoCodeMock = $this->getMockBuilder(Forms_Whitelabel_Bonuses_Promocodes_Code::class)
            ->setConstructorArgs([
                $this->whitelabel,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER
            ])
            ->onlyMethods(['issetPromoCode', 'saveUserPromoCode', 'getPromoCodeCampaign'])
            ->getMock();

        $this->whitelabelUser = $this->createWhitelabelUser();
    }

    /**
     * @test
     */
    public function affUserService_createUser__NoPromoCodeAndNoValidTokenWasApplied__ShouldNotCreateNewUserAff(): void
    {
        $parentAff = $this->createWhitelabelAff('aff');
        $this->createWhitelabelAff('sub-aff', $parentAff->id);

        $this->mockPromoCode();
        $this->process();

        $actual = $this->whitelabelUserAffRepository->findUserAffiliate(
            $this->whitelabel->id,
            $this->whitelabelUser->id
        );

        $this->assertFalse($this->affUserService->isCampaignApplicable());
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function affUserService_createUser__NoPromoCodeAndInvalidTokenWasApplied__ShouldNotCreateNewUserAff(): void
    {
        $parentAff = $this->createWhitelabelAff('aff');
        $this->createWhitelabelAff('sub-aff', $parentAff->id);

        $_COOKIE[Helpers_General::COOKIE_AFF_NAME] = 'invalid-token';

        $this->mockPromoCode();
        $this->process();

        $actual = $this->whitelabelUserAffRepository->findUserAffiliate(
            $this->whitelabel->id,
            $this->whitelabelUser->id
        );

        $this->assertFalse($this->affUserService->isCampaignApplicable());
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function affUserService_createUser__NoPromoCodeAndValidTokenWasApplied__ShouldCreateNewUserAff(): void
    {
        $affLeadAutoAccept = (bool) $this->whitelabel->affLeadAutoAccept;
        $parentAff = $this->createWhitelabelAff('aff');
        $this->createWhitelabelAff('sub-aff', $parentAff->id);

        $_COOKIE[Helpers_General::COOKIE_AFF_NAME] = $parentAff->token;

        $this->mockPromoCode();
        $this->process();

        $actual = $this->whitelabelUserAffRepository->findWhitelabelUserAffiliateByWhitelabelAff(
            $this->whitelabel->id,
            $this->whitelabelUser->id,
            $parentAff->id
        );

        $this->assertFalse($this->affUserService->isCampaignApplicable());
        $this->assertInstanceOf(WhitelabelUserAff::class, $actual);
        $this->assertEquals($affLeadAutoAccept, $actual->isAccepted);
    }

    /**
     * @test
     */
    public function affUserService_createUser__ValidAffPromoCodeAndValidSubAffTokenApplied__ShouldCreateNewUserAffWithParentAffAssignment(): void
    {
        $parentAff = $this->createWhitelabelAff('aff');
        $subAff = $this->createWhitelabelAff('sub-aff', $parentAff->id);

        $_COOKIE[Helpers_General::COOKIE_AFF_NAME] = $subAff->token;

        $this->whitelabelCampaignFixture->withWhitelabelAff($parentAff);

        $this->mockPromoCode(self::VALID_PROMO_CODE);
        $this->process();

        $actual = $this->whitelabelUserAffRepository->findWhitelabelUserAffiliateByWhitelabelAff(
            $this->whitelabel->id,
            $this->whitelabelUser->id,
            $parentAff->id
        );

        $this->assertTrue($this->affUserService->isCampaignApplicable());
        $this->assertInstanceOf(WhitelabelUserAff::class, $actual);
        $this->assertTrue($actual->isAccepted);
    }

    /**
     * @test
     */
    public function affUserService_createUser__ValidPromoCodeAndValidSubAffTokenApplied__ShouldCreateNewUserAffWithSubAffAssignment(): void
    {
        $affLeadAutoAccept = (bool) $this->whitelabel->affLeadAutoAccept;
        $parentAff = $this->createWhitelabelAff('aff');
        $subAff = $this->createWhitelabelAff('sub-aff', $parentAff->id);

        $_COOKIE[Helpers_General::COOKIE_AFF_NAME] = $subAff->token;

        $this->mockPromoCode(self::VALID_PROMO_CODE);
        $this->process();

        $actual = $this->whitelabelUserAffRepository->findWhitelabelUserAffiliateByWhitelabelAff(
            $this->whitelabel->id,
            $this->whitelabelUser->id,
            $subAff->id
        );

        $this->assertFalse($this->affUserService->isCampaignApplicable());
        $this->assertInstanceOf(WhitelabelUserAff::class, $actual);
        $this->assertEquals($affLeadAutoAccept, $actual->isAccepted);
    }

    /**
     * @throws Exception
     */
    private function process(): void
    {
        $this->affUserService->addCampaign($this->formPromoCodeMock->getPromoCodeCampaign());
        $this->affUserService->createUser($this->whitelabelUser->id);
    }

    private function createWhitelabelAff(string $login, int $whitelabelAffParentId = null): WhitelabelAff
    {
        $token = Lotto_Security::generate_aff_token($this->whitelabel->id);
        $subToken = Lotto_Security::generate_aff_token($this->whitelabel->id);

        $salt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash('123456', $salt);

        return $this->whitelabelAffRepository->insert(
            $this->whitelabel->to_array(),
            $whitelabelAffParentId,
            $token,
            $subToken,
            Helpers_General::get_default_language_id(),
            null,
            true,
            true,
            true,
            $login,
            $login . '@user.loc',
            $hash,
            $salt
        );
    }

    private function createWhitelabelUser(): WhitelabelUser
    {
        $this->whitelabelUserFixture->addRandomUser(100, 0);
        $this->whitelabelUserFixture->user->isActive = 1;
        $this->whitelabelUserFixture->user->isDeleted = 0;

        $user = new WhitelabelUser($this->whitelabelUserFixture->user->to_array(), false);
        $user->save();

        return $user;
    }

    private function mockPromoCode(string $scenario = self::NO_PROMO_CODE): void
    {
        switch ($scenario) {
            case self::VALID_PROMO_CODE:
                $campaign = $this->whitelabelCampaignFixture->makeOne();

                $this->formPromoCodeMock->expects($this->any())->method('issetPromoCode')->willReturn(true);

                $this->formPromoCodeMock
                    ->expects($this->once())
                    ->method('saveUserPromoCode');

                $this->formPromoCodeMock
                    ->expects($this->once())
                    ->method('getPromoCodeCampaign')
                    ->willReturn($campaign->to_array());

                $this->formPromoCodeMock->saveUserPromoCode($this->whitelabelUser->id);

                break;

            default:
            case self::INVALID_PROMO_CODE:
            case self::NO_PROMO_CODE:
                $this->formPromoCodeMock->expects($this->any())->method('issetPromoCode')->willReturn(false);
                $this->formPromoCodeMock->expects($this->never())->method('saveUserPromoCode');
                break;
        }
    }
}
