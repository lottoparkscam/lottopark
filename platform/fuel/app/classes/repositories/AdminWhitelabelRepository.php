<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Repositories\Orm\AbstractRepository;
use Models\AdminWhitelabel;
use Exception;
use Models\Whitelabel;

/** @method ?AdminWhitelabel findOneByAdminUserId(int $adminUserId) */
class AdminWhitelabelRepository extends AbstractRepository
{
    public function __construct(AdminWhitelabel $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws Exception when whitelabel doesn't exists for admin
     */
    public function getWhitelabelByAdminId(int $adminId): Whitelabel
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('admin_user_id', $adminId),
            new Model_Orm_Criteria_With_Relation('whitelabel')
        ]);

        /** @var AdminWhitelabel $adminWhitelabel */
        $adminWhitelabel = $this->findOne();

        return $adminWhitelabel->whitelabel;
    }
}
