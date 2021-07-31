<?php

namespace Studeo\Support;

use Illuminate\Database\Eloquent\Builder;

trait Findable
{
    /**
     * The column other than primary key to use to find the model record.
     */
    protected static string $findByKey = 'slug';

    /**
     * Get the query to find the model record identified by given key.
     *
     * @param string|int $key
     */
    public static function findByKeyQuery($key): Builder
    {
        $query = static::query();

        if (intval($key)) {
            return $query->where($query->getModel()->getKeyName(), $key);
        }

        return $query->where(static::$findByKey, $key);
    }

    /**
     * Find the model record by its given key.
     *
     * @param string|int $key
     */
    public static function findByKey($key): self
    {
        return static::findByKeyQuery($key)->first();
    }

    /**
     * Find the model record by given key or throw ModelNotFound exception.
     *
     * @param string|int $key
     */
    public static function findByKeyOrFail($key)
    {
        return static::findByKeyQuery($key)->firstOrFail();
    }
}