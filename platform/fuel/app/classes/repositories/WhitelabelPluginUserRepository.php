<?php

namespace Repositories;

use Models\WhitelabelPluginUser;
use Repositories\Orm\AbstractRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Orm\RecordNotFound;
use Carbon\Carbon;

/**
 * @method findOneByWhitelabelUserId(int $id)
 */
class WhitelabelPluginUserRepository  extends AbstractRepository
{
    public function __construct(WhitelabelPluginUser $model)
    {
        parent::__construct($model);
    }

    public function addPluginUser(int $whitelabelPluginId, int $whitelabelUserId, string $additionalData): bool
    {
        $newWhitelabelPluginUser = new WhitelabelPluginUser();
        $newWhitelabelPluginUser->whitelabelUserId = $whitelabelUserId;
        $newWhitelabelPluginUser->whitelabelPluginId = $whitelabelPluginId;
        $newWhitelabelPluginUser->data = $additionalData;
        return $newWhitelabelPluginUser->save();
    }

    public function getUserClickIdByWhitelabelUserId(int $userId): string
    {
        $pluginUser = $this->findOneByWhitelabelUserId($userId);
        if (empty($pluginUser)) {
            return '';
        }

        $additionalData = json_decode($pluginUser->data);
        return $additionalData->clickId ?? '';
    }

    public function getUserAffTokenByWhitelabelUserId(int $userId): string
    {
        $pluginUser = $this->findOneByWhitelabelUserId($userId);
        if (empty($pluginUser)) {
            return '';
        }

        $additionalData = json_decode($pluginUser->data);
        return $additionalData->affToken ?? '';
    }

    public function updateTimestamp(int $id): void
    {
        $this->db->update($this->model->get_table_name())
            ->set(['updated_at' => Carbon::now()])
            ->where('id', '=', $id)
            ->execute();
    }

    public function setAsInactive(int $id): void
    {
        $this->db->update($this->model->get_table_name())
            ->set(['is_active' => false])
            ->where('id', '=', $id)
            ->execute();
    }

    /**
     * @throws RecordNotFound
     */
    public function findOneByWhitelabelUserAndPluginId(int $userId, int $pluginId): WhitelabelPluginUser
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            new Model_Orm_Criteria_Where('whitelabel_plugin_id', $pluginId),
        ]);

        return $this->getOne();
    }

    public static function getActiveUsersCriterias(int $pluginId): array
    {
        $relation = WhitelabelPluginUser::get_table_name();

        // We fetch only active users for a given plugin ID or users that have not been added yet
        $nestedConditions = [
            'where' => [
                [
                    [
                        "$relation.whitelabel_plugin_id" => $pluginId,
                        "$relation.is_active" => true,
                    ],
                    'or' => [
                        "$relation.id" => NULL,
                    ]
                ],
            ]
        ];

        return [
            new Model_Orm_Criteria_With_Relation($relation, $nestedConditions),
        ];
    }
}
