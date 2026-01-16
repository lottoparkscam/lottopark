<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\WhitelabelApiNonce;
use Container;
use Wrappers\Db;

class WhitelabelApiNonceRepository extends AbstractRepository
{
    protected Db $db;

    public function __construct(WhitelabelApiNonce $model)
    {
        parent::__construct($model);
        $this->db = Container::get(Db::class);
    }

    public function deleteLogsBeforeTimestamp(string $timestamp): void
    {
        $this->db->delete($this->model::get_table_name())
            ->where('date', '<=', $timestamp)
            ->execute();
    }
}
