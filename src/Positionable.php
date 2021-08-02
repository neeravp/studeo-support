<?php

namespace Studeo\Support;

use Illuminate\Support\Str;

trait Positionable
{
    public static function bootPositionable(): void
    {
        static::creating(function ($model) {
            if ($model->isPositionable()) {
                $nextNewPosition = $model::count() + 1;
                $model->position = $model->position && $model->position !== $nextNewPosition
                    /**
                     * Reposition model records only if the newly created model
                     * is having a position specified other than nextNewPosition
                     */
                    ? $model->reposition($model->position, $nextNewPosition)
                    /**
                     * Else set the position as one greater than total no of existing records
                     */
                    : $model->getSiblingsCount() + 1;
            }
        });

        static::deleting(function ($model) {
            if ($model->isPositionable()) {
                /**
                 * Reposition only if the model record being deleted is not the last record
                 */
                if ($model->position !== $model::count()) {
                    $model->reposition($model::count(), $model->position);
                }
            }
        });
    }


    protected function getSiblingsCount(): int
    {
        if (method_exists($this, 'siblings')) {
            return $this->siblings()->count() ?: 0;
        }

        return static::count();
    }

    /**
     * Reposition the sibling model records.
     *
     * @throws Exception
     */
    public function reposition(int $newPosition, int $oldPosition): ?int
    {
        /**
         * Set the slug of the model being repositioned to a temp value removing the last digit
         * To avoid increment in slug for the siblings being repositioned
         */
        if ($this->id) {
            $this->update(['slug' => Str::limit($this->slug, strlen($this->slug) - 1, '-slug-temp')]);
        }

        /**
         * Deterine the change in position when updating
         */
        if (!$change = $newPosition - $oldPosition) {
            return null;
        }

        /**
         * Get all siblings which need to be repositioned
         */
        $siblings = $this->getRepositionableSiblings($change);

        $repositioned = collect([]);

        foreach ($siblings as $sibling) {
            $modified = tap($sibling)->update([
                'position' => $change < 0 ? $sibling->position + 1 : $sibling->position - 1,
            ]);

            $repositioned->push($modified);
        }

        /**
         * Check if there are duplicate positions for any of the siblings and throw error if so
         */
        if (!$unique = $repositioned->unique('position')->count() === count($siblings)) {
            throw new \Exception('Positionable exception: Cannot reposition');
        }

        return $newPosition;
    }

    protected function getRepositionableSiblings(int $change): array
    {
        return $change < 0
            ? $this->getPreceedingSiblings($change)
            : $this->getSucceedingSiblings($change);
    }

    protected function getPreceedingSiblings(int $change): array
    {
        $position = $this->id ? $this->position : get_class($this)::count() + 1;

        return $this->siblings()
            ->where('position', '<', $position)
            ->where('position', '>=', $position + $change)
            ->all();
    }

    protected function getSucceedingSiblings(int $change): array
    {
        $position = $this->id ? $this->position : get_class($this)::count() + 1;

        return $this->siblings()
            ->where('position', '>', $position)
            ->where('position', '<=', $position + $change)
            ->all();
    }

    public function isPositionable(): bool
    {
        return in_array(__TRAIT__, class_uses_recursive(get_called_class()));
    }
}