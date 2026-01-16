<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Container;
use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneByWhitelabelId(int $id)
 */
class WhitelabelSocialApiRepository extends AbstractRepository
{
    private Whitelabel $whitelabel;

    public function __construct(WhitelabelSocialApi $model)
    {
        parent::__construct($model);
        $this->whitelabel = Container::get('whitelabel');
    }

    public function findWhitelabelSocialSettingsBySocialType(string $type): ?WhitelabelSocialApi
    {
        $socialTypeTableName = SocialType::get_table_name();
        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation($socialTypeTableName),
            new Model_Orm_Criteria_Where('whitelabel_id', $this->whitelabel->id),
            new Model_Orm_Criteria_Where( $socialTypeTableName . '.type', $type),
        ]);

        /** @var WhitelabelSocialApi|null $whitelabelSocialApi */
        $whitelabelSocialApi = $this->findOne();
        return $whitelabelSocialApi;
    }

    public function countEnabledSocials(): int
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $this->whitelabel->id),
            new Model_Orm_Criteria_Where('is_enabled', true),
        ]);
        return $this->getCount();
    }
}
