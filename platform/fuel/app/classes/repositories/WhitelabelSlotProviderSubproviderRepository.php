<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelSlotProviderSubprovider;
use Services\Logs\FileLoggerService;
use Wrappers\Db;
use Throwable;

class WhitelabelSlotProviderSubproviderRepository extends AbstractRepository
{
    protected Db $db;
    private FileLoggerService $fileLoggerService;

    public function __construct(WhitelabelSlotProviderSubprovider $model, Db $db, FileLoggerService $fileLoggerService)
    {
        parent::__construct($model);
        $this->db = $db;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function getSubproviderIdsByWhitelabelSlotProviderId(int $whitelabelSlotProviderId): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['slot_subprovider_id']),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $whitelabelSlotProviderId)
        ]);

        return $this->getResultsForSingleField() ?? [];
    }

    private function update(int $whitelabelSlotProviderId, array $subProvidersIds, bool $isEnabled): void
    {
        $operator = $isEnabled ? 'IN' : 'NOT IN';

        try {
            $this->db->update($this->model->get_table_name())
                ->set(['is_enabled' => $isEnabled])
                ->where('whitelabel_slot_provider_id', '=', $whitelabelSlotProviderId)
                ->and_where('slot_subprovider_id', $operator, $subProvidersIds)
                ->and_where('force_disable', '=', false)
                ->execute();
        } catch (Throwable $e) {
            $this->fileLoggerService->error(
                "Cannot update whitelabel_slot_provider_subprovider is_enabled field " . $e->getMessage()
            );
        }
    }

    public function disable(int $whitelabelSlotProviderId, array $subProvidersIds): void
    {
        $this->update($whitelabelSlotProviderId, $subProvidersIds, false);
    }

    public function enable(int $whitelabelSlotProviderId, array $subProvidersIds): void
    {
        $this->update($whitelabelSlotProviderId, $subProvidersIds, true);
    }
}
