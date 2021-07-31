<?php

namespace Studeo\Support;

use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Boolean;

trait Sluggable
{
    protected static string $slugFromColumn = 'name';

    public static function bootSluggable():void
    {
        static::creating(function ($model) {
            $model->slug = $model->slug ?? Str::slug($model->slugFrom());
        });
        static::updating(function ($model) {
            if($model->isDirty(static::$slugFromColumn)) {
                $model->slug = Str::slug($model->slugFrom());
            }
        });
    }

    /**
     * Get the value to generate slug for the model record.
     */
    public function slugFrom(): string
    {
        return $this->{static::$slugFromColumn};
    }

    /**
     * Determine whether the model instance is sluggable or not.
     */
    public function isSluggable(): bool
    {
        return in_array(static::class, class_uses_recursive(get_called_class()));
    }

    /**
     * Find a model instance from the given slug.
     */
    public static function findBySlug(string $slug): self
    {
        return static::whereSlug($slug)->first();
    }

    /**
     * Find a model instance from the given slug or throw ModelNotFound exception.
     */
    public static function findBySlugOrFail(string $slug): self
    {
        return static::whereSlug($slug)->firstOrFail();
    }
}