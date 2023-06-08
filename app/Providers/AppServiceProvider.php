<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Collection::macro('objectify', fn (): Collection => (new class($this) extends Collection
        {
            public function __get($key)
            {
                return $this->get($key);
            }

            public function __set($key, $value)
            {
                $this->put($key, $value);
            }
        }));

        Collection::macro(
            'recursive',
            fn (
                int $depth = PHP_INT_MAX,
                ?array $objectifies = [],
                ?callable $keyTransformer = null,
                ?callable $valueTransformer = null
                // Use the map mapWithKeys to iterate over the items in the collection.
            ): Collection => $this->mapWithKeys(function ($item, $key) use (
                $depth, $objectifies, $keyTransformer, $valueTransformer
            ) {
                $key = $depth >= 0 && $keyTransformer !== null ? $keyTransformer($key) : $key;

                if ($depth >= 0 && $valueTransformer !== null) {
                    $item[$key] = $valueTransformer($item[$key]);
                }

                // If the depth is 0 or the item is not a collection, array, or object, return the item.
                if ($depth <= 0 || ! ($item instanceof Collection || is_array($item) || is_object($item))) {
                    return [$key => $item];
                }

                // Create a new anonymous class that extends the Collection class and overrides the __get and __set
                // magic methods. To be able to access the collection items as if they were properties of an object.
                if (($objectifies === null || count($objectifies) === 0) && $item instanceof Collection) {
                    $item = new static($item);
                } elseif ((in_array('collection', $objectifies) && $item instanceof Collection) ||
                    (in_array('object', $objectifies) && is_object($item)) ||
                    (in_array('array', $objectifies) && is_array($item))
                ) {
                    $item = $this->objectify(new static($item));
                } else {
                    $item = new class(new static($item)) extends Collection
                    {
                    };
                }

                // Lastly, apply the "recursive" method to new transformed Collection instance.
                return [
                    $key => $item->recursive($depth - 1, $objectifies, $keyTransformer, $valueTransformer),
                ];
            })
        );

        Collection::macro(
            'recursiveTransformValues',
            fn (callable $transformer, int $depth = PHP_INT_MAX): Collection => $this->recursive(
                $depth, [], $transformer
            )
        );
        Collection::macro(
            'recursiveTransformKeys',
            fn (callable $transformer, int $depth = PHP_INT_MAX): Collection => $this->recursive(
                $depth, [], $transformer
            )
        );

        Collection::macro('camelCaseKeys', fn ($depth = PHP_INT_MAX): Collection => $this->recursive(
            $depth, [], fn ($key) => gettype($key) === 'integer' ? $key : Str::camel($key))
        );
        Collection::macro('snakeCaseKeys', fn ($depth = PHP_INT_MAX): Collection => $this->recursive(
            $depth, [], fn ($key) => gettype($key) === 'integer' ? $key : Str::snake($key))
        );
        Collection::macro('kebabCaseKeys', fn ($depth = PHP_INT_MAX): Collection => $this->recursive(
            $depth, [], fn ($key) => gettype($key) === 'integer' ? $key : Str::kebab($key))
        );
    }
}
