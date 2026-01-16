<?php

namespace Repositories;

use Carbon\Carbon;
use Container;
use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelPluginLog;

/** @method deleteRecordsOlderThanXDays(int $days, string $dateColumn = 'date'): void */
class WhitelabelPluginLogRepository extends AbstractRepository
{
    public const TYPE_ERROR = 3;

    public function __construct(WhitelabelPluginLog $model)
    {
        parent::__construct($model);
    }

    public function removeLogById(int $logId): int
    {
        return $this->db->delete($this->model::get_table_name())
            ->where('id', '=', $logId)
            ->execute();
    }

    public function countPrimeadsRegisteryLog(): int
    {
        return count($this->getPrimeadsRegisterLogs());
    }

    public function countPrimeadsPurchaseLog(): int
    {
        return count($this->getPrimeadsPurchaseLogs());
    }

    public function getPrimeadsRegisterLogs(int $limit = null): array
    {
        return $this->getPrimeadsLogsByGoal('reg', $limit);
    }

    public function getLastTenPrimeadsRegisterLogs(): array
    {
       return $this->getPrimeadsRegisterLogs(10);
    }

    public function getLastTenPrimeadsPurchaseLogs(): array
    {
       return $this->getPrimeadsPurchaseLogs(10);
    }

    public function getPrimeadsPurchaseLogs(int $limit = null): array
    {
        return $this->getPrimeadsLogsByGoal('rs', $limit);
    }

    public function getPrimeadsLogsByGoal(string $goal, int $limit = null): array
    {
        /** ten logs is limit which we received from primeads */
        $whitelabelPluginRepository = Container::get(WhitelabelPluginRepository::class);
        /** @var WhitelabelPlugin $whitelabelPlugin */
        $whitelabelPlugin = $whitelabelPluginRepository->findOneByPlugin(WhitelabelPlugin::PRIMEADS_NAME);
        $query = $this->db->selectArray(['id', 'message'])
            ->from($this->model::get_table_name())
            ->where('whitelabel_plugin_id', '=',  $whitelabelPlugin->id)
            ->and_where('message', 'like', '%goal=' . $goal . '%');
        if (isset($limit)) {
            $query->limit($limit);
        }
        /** @phpstan-ignore-next-line */
        return $query->execute()->as_array();
    }

    public function addErrorLog(string $pluginName, string $message): void
    {
        $this->addLog($pluginName, $message, Carbon::now()->format('Y-m-d H:i:s'), self::TYPE_ERROR);
    }

    private function addLog(string $pluginName, string $message, string $date, int $type): void
    {
        $whitelabelPluginRepository = Container::get(WhitelabelPluginRepository::class);
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');

        $whitelabelPlugin = $whitelabelPluginRepository->findPluginByNameAndWhitelabelId($pluginName, $whitelabel->id);
        if (empty($whitelabelPlugin)) {
            return;
        }

        $whitelabelPluginLog = new WhitelabelPluginLog();
        $whitelabelPluginLog->whitelabelPluginId = $whitelabelPlugin->id;
        $whitelabelPluginLog->date = $date;
        $whitelabelPluginLog->type = $type;
        $whitelabelPluginLog->message = $message;
        $whitelabelPluginLog->save();
    }
}
