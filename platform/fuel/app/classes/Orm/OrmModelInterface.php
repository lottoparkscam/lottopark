<?php

namespace Classes\Orm;

use Orm\RecordNotFound;

interface OrmModelInterface
{
    /**
     * @return static
     */
    public static function dao();

    /**
     * @param Model_Orm_Criteria $criteria
     *
     */
    public function push_criteria(Model_Orm_Criteria $criteria): OrmModelInterface;

    /**
     * @param Model_Orm_Criteria[] $criterias
     *
     */
    public function push_criterias(array $criterias): OrmModelInterface;

    /**
     * @throws RecordNotFound
     */
    public function get_one(): OrmModelInterface;

    public function find_one(): ?OrmModelInterface;

    /**
     * @param int|null $limit
     * @param int $offset
     *
     * @return array
     */
    public function get_results(int $limit = null, int $offset = 0): array;

    public function getCount($column = null, $distinct = true);

    public function save($cascade = null, $use_transaction = false): bool;

    public function store(OrmModelInterface $model): void;

    public function exists($value, string $field = 'id'): bool;

    public function get_by_id(int $id, array $relations = []);

    public function reload(): void;

    /**
     * @return bool
     */
    public function create();

    /**
     * @return bool
     */
    public function update();
}
