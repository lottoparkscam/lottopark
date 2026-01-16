<?php

namespace Classes\Orm\Interfaces;

use Orm\RecordNotFound;

interface Model_Orm_Interfaces_Read
{
    /**
     * @return static|Model_Orm_Interfaces_Read
     * @throws RecordNotFound
     */
    public function get_one(): Model_Orm_Interfaces_Read;

    /**
     * @return static|Model_Orm_Interfaces_Read|null
     */
    public function find_one(): ?Model_Orm_Interfaces_Read;

    /**
     * @param int|null $limit
     * @param int $offset
     *
     * @return static[]|Model_Orm_Interfaces_Read[]|array
     */
    public function get_results(int $limit = null, int $offset = 0): array;

    public function get_count(): int;

    public function exists($value, string $field = 'id'): bool;

    public function get_by_id(int $id, array $relations = []): Model_Orm_Interfaces_Read;

    public function reload(): void;

    public function set_order_by(string $sort_field = 'id', string $sort_order = 'asc'): Model_Orm_Interfaces_Read;
}
