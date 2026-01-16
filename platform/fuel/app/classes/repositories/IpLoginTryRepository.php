<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Repositories\Orm\AbstractRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\IpLoginTry;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class IpLoginTryRepository extends AbstractRepository
{
    public function __construct(IpLoginTry $model)
    {
        parent::__construct($model);
    }

    public function findByIp(string $ip): ?IpLoginTry
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('ip', $ip),
            new Model_Orm_Criteria_Order('last_login_try_at', 'ASC')
        ]);

        return $this->findOne();
    }

    public function updateById(int $id, ?int $ipLoginTryCount = null, ?string $lastLoginTryAt = null): ?IpLoginTry
    {
        /** @var IPLoginTry $ipLoginTry */
        $ipLoginTry = IpLoginTry::find($id);

        if (!$ipLoginTry) {
            return null;
        }

        if (!is_null($ipLoginTryCount)) {
            $ipLoginTry->loginTryCount = $ipLoginTryCount;
        }

        if (!is_null($lastLoginTryAt)) {
            $ipLoginTry->lastLoginTryAt = $lastLoginTryAt;
        }

        $ipLoginTry->save();
        
        return $ipLoginTry;
    }

    public function insert(array $credentials): IpLoginTry
    {
        $ipLoginTry = new IpLoginTry;
        $ipLoginTry->ip = $credentials['ip'];
        $ipLoginTry->lastLoginTryAt = $credentials['last_login_try_at'];
        $ipLoginTry->loginTryCount = $credentials['login_try_count'];

        $ipLoginTry->save();

        return $ipLoginTry;
    }
}
