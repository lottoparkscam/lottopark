<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Exception;
use Models\WhitelabelUserSocial;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneBySocialUserId(string $socialId)
 * @method findOneById(int $whitelabelUserSocialId)
 */
class WhitelabelUserSocialRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserSocial $model)
    {
        parent::__construct($model);
    }

    public function findEnabledByWhitelabelUserIdAndWhitelabelSocialAppId(int $userId, int $whitelabelSocialAppId): ?WhitelabelUserSocial
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_user'),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            new Model_Orm_Criteria_Where('whitelabel_social_api_id', $whitelabelSocialAppId),
            new Model_Orm_Criteria_Where('whitelabel_user.is_deleted', false),
        ]);

        /** @var WhitelabelUserSocial|null $whitelabelUserSocial */
        $whitelabelUserSocial = $this->findOne();
        return $whitelabelUserSocial;

    }

    public function findEnabledByUserSocialIdAndWhitelabelSocialAppId(string $userSocialId, int $whitelabelSocialAppId): ?WhitelabelUserSocial
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('whitelabel_user'),
            new Model_Orm_Criteria_Where('social_user_id', $userSocialId),
            new Model_Orm_Criteria_Where('whitelabel_social_api_id', $whitelabelSocialAppId),
            new Model_Orm_Criteria_Where('whitelabel_user.is_deleted', false),
        ]);

        /** @var WhitelabelUserSocial|null $whitelabelUserSocial */
        $whitelabelUserSocial = $this->findOne();
        return $whitelabelUserSocial;
    }

    /**
     * @throws Exception
     */
    public function insert(array $credentials): ?WhitelabelUserSocial
    {
        $whitelabelUserSocial = new WhitelabelUserSocial();
        $whitelabelUserSocial->whitelabelUserId = $credentials['whitelabelUserId'];
        $whitelabelUserSocial->socialUserId = $credentials['socialUserId'];
        $whitelabelUserSocial->isConfirmed = $credentials['isConfirmed'];
        $whitelabelUserSocial->whitelabelSocialApiId = $credentials['whitelabelSocialApiId'];
        $whitelabelUserSocial->activationHash = $credentials['activationHash'] ?? null;
        $whitelabelUserSocial->lastHashSentAt = $credentials['lastHashSentAt'] ?? null;
        $isSuccessfullyAdded = $whitelabelUserSocial->save();
        if ($isSuccessfullyAdded) {
            return $whitelabelUserSocial;
        }
        return null;
    }

    public function updateHash(int $whitelabelUserSocialId, string $hash, string $lastHashSentAt): void
    {
        $this->db->update($this->model->get_table_name())
            ->set([
                'activation_hash' => $hash,
                'last_hash_sent_at' => $lastHashSentAt,
                ])
            ->where('id', '=', $whitelabelUserSocialId)
            ->execute();
    }

    public function confirmSocialLogin(int $whitelabelUserId, int $whitelabelSocialAppId): void
    {
        $this->db->update($this->model->get_table_name())
            ->set(['is_confirmed' => true])
            ->where('whitelabel_social_api_id', '=', $whitelabelSocialAppId)
            ->and_where('whitelabel_user_id', '=', $whitelabelUserId)
            ->execute();
    }

    public function removeUnusedHashAndHashSentDate(int $whitelabelUserId, int $whitelabelSocialAppId): void
    {
        $this->db->update($this->model->get_table_name())
            ->set([
                'activation_hash' => null,
                'last_hash_sent_at' => null
            ])
            ->where('is_confirmed', '=',  true)
            ->and_where('whitelabel_social_api_id', '=', $whitelabelSocialAppId)
            ->and_where('whitelabel_user_id', '=', $whitelabelUserId)
            ->execute();
    }
}
