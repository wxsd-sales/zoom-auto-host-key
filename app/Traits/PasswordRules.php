<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Rule;
use Laravel\Fortify\Rules\Password;

/**
 * Get the validation rules used to validate passwords.
 */
trait PasswordRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', new Password, 'confirmed'];
    }
}
