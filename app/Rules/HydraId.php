<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HydraId implements ValidationRule
{
    private string $id;

    private bool $strict;

    public function __construct(string $id, bool $strict = false)
    {
        $this->strict = $strict;
        $this->id = $id;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valueDecoded = base64_decode($value) !== false ? base64_decode($value) : $value;
        $idDecoded = base64_decode($this->id) !== false ? base64_decode($this->id) : $this->id;

        if ($this->strict) {
            $isValid = $valueDecoded === $idDecoded;
        } else {
            $valueBase = implode('/', array_slice(explode('/', $valueDecoded), -2));
            $idBase = implode('/', array_slice(explode('/', $idDecoded), -2));
            $isValid = strcasecmp($valueBase, $idBase) === 0;
        }

        if (! $isValid) {
            $fail("The :attribute field must represent Hydra resource, $this->id.");
        }
    }
}
