<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelPlugin;
use Repositories\Orm\AbstractRepository;

/**
 * @method Null|WhitelabelPlugin findOneByPlugin(string $plugin)
 */
class WhitelabelPluginRepository extends AbstractRepository
{
    public function __construct(WhitelabelPlugin $model)
    {
        parent::__construct($model);
    }

    public function findPluginByNameAndWhitelabelId(string $pluginName, int $whitelabelId): ?WhitelabelPlugin
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id', 'whitelabel_id', 'plugin', 'is_enabled', 'options']),
            new Model_Orm_Criteria_Where('plugin', $pluginName),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ])->findOne();
    }

    public function getAllEnabledPlugins(string $pluginName): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('plugin', $pluginName),
            new Model_Orm_Criteria_Where('is_enabled', true),
        ]);

        return $this->getResults();
    }
}
