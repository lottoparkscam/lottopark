<?php

namespace Repositories;


use Models\AdminUser;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneByRoleId(int $id)
 */
class AdminUserRepository extends AbstractRepository
{
    public function __construct(AdminUser $model)
    {
        parent::__construct($model);
    }
}
