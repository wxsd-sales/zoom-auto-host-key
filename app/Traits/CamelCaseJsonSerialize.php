<?php

namespace App\Traits;

/**
 * Serialize instance to JSON with top level Camel Case keys.
 *
 * @template TKey of array-key
 * @template TValue
 */
trait CamelCaseJsonSerialize
{
    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    abstract public function toArray();

    /**
     * Serialize instance to JSON with top level Camel Case keys.
     */
    public function jsonSerialize($raw = false): array
    {
        return $raw ? $this->toArray() : collect($this)->camelCaseKeys(0)->toArray();
    }
}
