<?php

namespace App\Traits;

/**
 * Split a delimited string into individually trimmed values.
 */
trait DelimitedToArray
{
    /**
     * Split a delimited string into individually trimmed values.
     */
    protected static function delimitedToArray(string $value, string $delimiter = ','): array
    {
        return array_map('trim', explode($delimiter, $value));
    }
}
