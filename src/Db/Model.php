<?php

namespace Lazy\Db;

use ReflectionClass;

/**
 * The base ORM model class.
 */
abstract class Model
{
    /**
     * The ORM model table.
     *
     * @var string
     */
    public static $table;

    /**
     * Get the ORM model table.
     *
     * @return string
     */
    public static function getTable()
    {
        if (static::$table) {
            return static::$table;
        }

        $class = new ReflectionClass(static::class);

        return strtolower($class->getShortName()).'s';
    }
}
