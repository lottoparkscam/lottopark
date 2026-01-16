<?php

namespace Services\SocialMediaConnect;

use Container;
use Exception;
use Exceptions\SocialMedia\FillRegisterFormException;
use Exceptions\SocialMedia\UserProfileWithEmptyEmailException;
use Exceptions\SocialMedia\UserIsCorrectlyConnectedException;
use Exceptions\SocialMedia\UserIsNotConnectedOrIsDeletedException;
use Exceptions\SocialMedia\SocialUserEmailEqualsEmailWhichHaveSocialConnectionException;
use Fuel\Core\Session;
use Hybridauth\User\Profile;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Logs\FileLoggerService;

class ConnectService
{
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelUserSocialRepository $whitelabelUserSocialRepository;
    private FileLoggerService $fileLoggerService;

    public function __construct
    (
        WhitelabelUserRepository $whitelabelUserRepository,
        WhitelabelUserSocialRepository $whitelabelUserSocialRepository,
        FileLoggerService $fileLoggerService,
    )
    {
        $this->whitelabelUserRepository = $whitelabelUserRepository;
        $this->whitelabelUserSocialRepository = $whitelabelUserSocialRepository;
        $this->fileLoggerService = $fileLoggerService;
    }

    /**
     * @throws UserProfileWithEmptyEmailException
     * @throws FillRegisterFormException
     * @throws UserIsNotConnectedOrIsDeletedException
     * @throws UserIsCorrectlyConnectedException
     * @throws SocialUserEmailEqualsEmailWhichHaveSocialConnectionException
     */
    public function connect(Profile $user, int $whitelabelSocialApiId): void
    {
        /** The order of the functions matters */
        $this->throwUserIsCorrectlyConnectedException($user, $whitelabelSocialApiId);
        $this->throwUserWithIncorrectSocialIdException($user, $whitelabelSocialApiId);
        $this->throwSocialUserProfileWithEmptyEmailException($user);
        $this->throwFillRegisterFormException($user);
        $this->throwUserIsNotConnectedOrIsDeletedException($user, $whitelabelSocialApiId);
    }

    /**
     * @throws UserIsNotConnectedOrIsDeletedException
     * @throws Exception
     */
    private function throwUserIsNotConnectedOrIsDeletedException(Profile $user, int $whitelabelSocialApiId): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabelUser = $this->whitelabelUserRepository->findUserByEmailAndWhitelabelId($user->email, $whitelabel->id);
        $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUser->id, $whitelabelSocialApiId);
        if (empty($whitelabelUserSocial)) {
            throw new UserIsNotConnectedOrIsDeletedException();
        }
    }

    /**
     * @throws FillRegisterFormException
     * @throws Exception
     */
    private function throwFillRegisterFormException(Profile $user): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabelUser = $this->whitelabelUserRepository->findUserByEmailAndWhitelabelId($user->email, $whitelabel->id);
        $whitelabelUserNotExists = empty($whitelabelUser);
        $whitelabelUserExists = !$whitelabelUserNotExists;
        $whitelabelUserIsDeleted = $whitelabelUserExists && $whitelabelUser->isDeleted;
        if ($whitelabelUserNotExists || $whitelabelUserIsDeleted) {
            throw new FillRegisterFormException();
        }
    }

    /**
     * @throws UserIsCorrectlyConnectedException
     */
    private function throwUserIsCorrectlyConnectedException(Profile $user, int $whitelabelSocialApiId): void
    {
        $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findEnabledByUserSocialIdAndWhitelabelSocialAppId($user->identifier, $whitelabelSocialApiId);
        if (!empty($whitelabelUserSocial) && !$whitelabelUserSocial->whitelabelUser->isDeleted) {
            throw new UserIsCorrectlyConnectedException();
        }
    }


    /**
     * @throws SocialUserEmailEqualsEmailWhichHaveSocialConnectionException
     */
    private function throwUserWithIncorrectSocialIdException(Profile $user, int $whitelabelSocialApiId): void
    {
        /**
         * @link https://gginternational.slite.com/app/docs/9drAGR7Vx2rsGL#21a5979a
         */
        if ($this->userDoesNotHaveEmail($user)) {
            return;
        }

        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabelUser = $this->whitelabelUserRepository->findUserByEmailAndWhitelabelId($user->email, $whitelabel->id);
        if (!empty($whitelabelUser)) {
            /** @var WhitelabelUserSocial|null $whitelabelUserSocial */
            $whitelabelUserSocial = $this->whitelabelUserSocialRepository->findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId($whitelabelUser->id, $whitelabelSocialApiId);
            if (!empty($whitelabelUserSocial) && $whitelabelUserSocial->socialUserId !== $user->identifier) {
                throw new SocialUserEmailEqualsEmailWhichHaveSocialConnectionException();
            }
        }
    }

    /**
     * @throws UserProfileWithEmptyEmailException
     */
    private function throwSocialUserProfileWithEmptyEmailException(Profile $user): void
    {
        if ($this->userDoesNotHaveEmail($user)) {
            throw new UserProfileWithEmptyEmailException();
        }
    }

    private function userDoesNotHaveEmail(Profile $profile): bool
    {
        return empty($profile->email);
    }

    /**
     * @throws Exception
     */
    public function createSocialConnection(string $userToken, int $whitelabelId): void
    {
        $whitelabelSocialApiId = Session::get('whitelabelSocialApiId');
        $socialUserId = Session::get('socialUserId');
        /** @var WhitelabelUser|null $user */
        $user = $this->whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabelId);
        if (empty($whitelabelSocialApiId) || empty($socialUserId) || empty($user)) {
             $this->fileLoggerService->error('Whitelabel_user_social is not added while first register for whitelabel_user.id: ' . empty($user) ? 'user id not exists' : $user->id);
            return;
        }
        $credentials = [
            'whitelabelUserId' => $user->id,
            'whitelabelSocialApiId' => $whitelabelSocialApiId,
            'socialUserId' => Session::get('socialUserId'),
            'isConfirmed' => true,
        ];
        try {
            /** @var WhitelabelUserSocial|null $whitelabelSocialUser */
            $whitelabelSocialUser = $this->whitelabelUserSocialRepository->insert($credentials);
        } catch (Exception) {
            throw new Exception('whitelabel user social is not added during creating social connection');
        }
        if (empty($whitelabelSocialUser)) {
            $this->fileLoggerService->error('Whitelabel_user_social is not added while first register for whitelabel_user.id: ' . $user->id);
        }
    }
}
