<?php

namespace Repositories\Traits;

use Carbon\Carbon;

trait DeleteRecordsOlderThanXDaysTrait
{
    public function deleteRecordsOlderThanXDays(int $daysCount, string $dateColumn = 'date'): void
    {
        $today = new Carbon($this->model->getTimezoneForField($dateColumn));
        $dateBeforeXDays = $today->subDays($daysCount)->format('Y-m-d');

        $this->db->delete($this->model::get_table_name())
            ->where($this->db->expr("date($dateColumn)"), '<=', $dateBeforeXDays)
            ->execute();
    }
}
