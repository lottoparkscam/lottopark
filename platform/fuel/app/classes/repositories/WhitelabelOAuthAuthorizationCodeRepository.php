<?php

declare(strict_types=1);

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelOAuthAuthorizationCode;
use Repositories\Orm\AbstractRepository;

class WhitelabelOAuthAuthorizationCodeRepository extends AbstractRepository
{
    public function __construct(WhitelabelOAuthAuthorizationCode $model)
    {
        parent::__construct($model);
    }

    public function findOneByClientIdAndUserId(string $clientId, int $userId): ?WhitelabelOAuthAuthorizationCode
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('client_id', $clientId),
            new Model_Orm_Criteria_Where('user_id', $userId),
            new Model_Orm_Criteria_Order('client_id', 'desc')
        ]);

        /** @var WhitelabelOAuthAuthorizationCode $oAuthAuthCode */
        $oAuthAuthCode = $this->findOne();

        return $oAuthAuthCode;
    }
}
