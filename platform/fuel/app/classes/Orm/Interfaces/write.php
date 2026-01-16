<?php

namespace Classes\Orm\Interfaces;

use Classes\Orm\AbstractOrmModel;
use Classes\Orm\OrmModelInterface;

interface Model_Orm_Interfaces_Write
{
    public function store(OrmModelInterface $model): void;

    public static function first_or_create(array $props, array $where = []): AbstractOrmModel;
}
