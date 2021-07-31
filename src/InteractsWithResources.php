<?php

namespace Studeo\Support;

use ReflectionClass;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithResources
{
    public static array $resources = [];

    /**
     * Register the given resources.
     */
    public static function registerResources(array $resources = []): self
    {
        static::$resources = array_unique(array_merge(static::$resources, $resources));

        return new static;
    }

    /**
     * Get all registered resources.
     */
    public static function resources(): array
    {
        return static::$resources;
    }

    /**
     * Get the class of registered resource from the given URI key.
     */
    public static function resourceForKey(string $uriKey): string
    {
        return collect(static::resources())
            ->first(fn ($resource) => $resource::uriKey() === $uriKey);
    }

    /**
     * Prepare the resource information data for frontend consumption.
     */
    public static function resourceInfo(): array
    {
        return collect(static::resources())
            ->map(fn ($resource) => [
                'uriKey'         => $resource::uriKey(),
                'perPageOptions' => $resource::perPageOptions()
            ])->values()->all();
    }

    public static function registerResourcesFrom(string $directory)
    {
        $namespace = static::namespace();
        $resources = [];
        foreach ((new Finder)->in($directory)->files() as $resource) {
            $resource = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($resource->getPathname(), static::pathMethod()() . DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($resource, Model::class) && !(new ReflectionClass($resource))->isAbstract()) {
                $resources[] = $resource;
            }
        }

        return $resources;
    }
}