<?php


namespace Wrappers;

use Classes\Orm\AbstractOrmModel;
use Orm\Query;

class Orm
{
    public static function disableCaching(): void
    {
        // It sets private value only in Query object
        Query::caching(false);
        AbstractOrmModel::setGlobalCaching(false);
    }

    public static function enableCaching(): void
    {
        // It sets private value only in Query object
        Query::caching(true);
        AbstractOrmModel::setGlobalCaching(true);
    }
}