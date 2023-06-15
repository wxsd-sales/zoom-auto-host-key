<?php

namespace App\Traits;

/**
 * Split a string by a separator into trimmed individual values.
 */
trait DelimitedToArray
{
    /**
     * Split a string by a separator into trimmed individual values.
     */
    protected static function delimitedToArray(string $value, string $delimiter = ','): array
    {
        return array_map('trim', explode($delimiter, $value));
    }
}
