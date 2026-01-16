<?php

namespace Repositories;

use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Container;
use Exceptions\Generic\WhitelabelNotFound;
use Models\Whitelabel;
use Models\WhitelabelLottery;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Orm\RecordNotFound;
use Repositories\Orm\AbstractRepository;

/**
 * @method WhitelabelLottery findOneById(int $id)
 * @method WhitelabelLottery findOneByLotteryId(int $id)
 */
class WhitelabelLotteryRepository extends AbstractRepository
{
    public function __construct(WhitelabelLottery $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws RecordNotFound
     */
    public function getOneByLotteryIdForWhitelabel(int $lotteryId, int $whitelabelId): WhitelabelLottery
    {
        $this->pushCriterias(
            [
                new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
                new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            ]
        );

        return $this->getOne();
    }

    public function isDisabledForCurrentWhitelabelByLotterySlug(string $lotterySlug): bool
    {
        /** @var Whitelabel|null $whitelabel */
        $whitelabel = Container::get('whitelabel');

        if (empty($whitelabel)) {
            throw new WhitelabelNotFound();
        }

        $whitelabelId = $whitelabel->id;

        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('lottery'),
            new Model_Orm_Criteria_Where('lottery.slug', $lotterySlug),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_enabled', true),
            new Model_Orm_Criteria_Where('lottery.is_enabled', true),
        ]);

        return $this->getCount() === 0;
    }
}
