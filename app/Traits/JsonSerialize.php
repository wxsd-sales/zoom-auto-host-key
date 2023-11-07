<?php

namespace App\Traits;

use App\Enums\WordCaseEnum as WordCase;

/**
 * Serialize instance to JSON with top level keys transformed to a particular word case (defaults to camel case).
 *
 * @template TKey of array-key
 * @template TValue
 */
trait JsonSerialize
{
    protected WordCase $jsonSerializationCase = WordCase::CAMEL;

    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    abstract public function toArray();

    /**
     * Serialize instance to JSON with top level keys transformed to a particular word case (defaults to camel case).
     */
    public function jsonSerialize($raw = false): array
    {
        return $raw
            ? $this->toArray()
            : collect($this->toArray())->{$this->jsonSerializationCase->value.'CaseKeys'}(0)->toArray();
    }
}
