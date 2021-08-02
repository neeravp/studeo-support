<?php

namespace Studeo\Support;

trait Activable
{
    /**
     * Get the duration since when the user has been active.
     */
    public function getActiveAttribute(): string
    {
        return $this->active_since->diffForHumans();
    }

    /**
     * Determine whether the user is active or not.
     *
     * @return boolean
     */
    public function isActive()
    {
        return !is_null($this->active_since);
    }

    /**
     * Mark the model as active.
     */
    public function activate(): self
    {
        $this->forceFill([
            'active_since' => $this->freshTimestamp()
        ])->save();

        return $this;
    }

    /**
     * Mark the model as in-active.
     */
    public function deactivate(): self
    {
        $this->forceFill([
            'active_since' => null
        ])->save();

        return $this;
    }

    /**
     * Determine whether the model instance is activable or not.
     */
    public function isActivable(): bool
    {
        return in_array(__TRAIT__, class_uses_recursive(get_called_class()));
    }

    /**
     * Get the attributes which need to be cast to primitive types.
     */
    public function getCasts(): array
    {
        if ($this->isActivable()) {
            $this->casts = array_merge($this->casts, ['active_since' => 'datetime']);
        }

        return $this->casts;
    }
}