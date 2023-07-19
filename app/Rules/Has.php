<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JsonException;

class Has implements ValidationRule
{
    private readonly mixed $required;

    private readonly ?string $valueSeparator;

    /**
     * @var callable|null
     */
    private $valueComparer;

    public function __construct(mixed $required, ?string $valueSeparator = ' ', callable $valueComparer = null)
    {
        $this->required = gettype($required) === 'array' ? $required : [$required];
        $this->valueSeparator = $valueSeparator;
        $this->valueComparer = $valueComparer;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     *
     * @throws JsonException
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (gettype($value) === 'string' && $this->valueSeparator !== null) {
            $value = explode($this->valueSeparator, $value);
        } elseif (gettype($value) === 'string' && $this->valueSeparator === null) {
            $value = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        }

        $value = gettype($value) === 'array' ? $value : [$value];

        $missing = array_values($this->valueComparer !== null
            ? (array_is_list($value)
                ? array_udiff($this->required, $value, $this->valueComparer)
                : array_udiff_assoc($this->required, $value, $this->valueComparer)
            )
            : (array_is_list($value)
                ? array_diff($this->required, $value)
                : array_diff_assoc($this->required, $value)
            )
        );

        if (count($missing) > 0) {
            $missingStr = implode(', ', array_map('json_encode', $missing));
            $fail("The :attribute field must include $missingStr.");
        }
    }
}
