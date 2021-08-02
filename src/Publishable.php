<?php

namespace Studeo\Support;

use Illuminate\Database\Eloquent\Builder;

trait Publishable
{
    /**
     * Query scope to get all published records.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('publish_at');
    }

    /**
     * Mark the model as published.
     */
    public function publish(): self
    {
        return tap($this)->update(['publish_at' => now()->subMinute()]);
    }

    /**
     * Mark the model as un-published.
     */
    public function unPublish(): self
    {
        return tap($this)->update(['publish_at' => null]);
    }

    /**
     * Determine whether the model instance is publishable or not.
     */
    public function isPublishable(): bool
    {
        return in_array(__TRAIT__, class_uses_recursive(get_called_class()));
    }

    /**
     * Get the attributes which need to be cast to primitive types.
     */
    public function getCasts(): array
    {
        if ($this->isPublishable()) {
            $this->casts = array_merge($this->casts, ['publish_at' => 'datetime']);
        }

        return $this->casts;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->isPublishable() ? !is_null($this->published_at) : false;
    }
}