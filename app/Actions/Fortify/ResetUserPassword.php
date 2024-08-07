<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Traits\PasswordRules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function reset(User $user, array $input): void
    {
        $inputValidator = Validator::make($input, [
            'password' => $this->passwordRules(),
        ]);
        $validatedInput = $inputValidator->validate();

        $user->forceFill([
            'password' => Hash::make($validatedInput['password']),
        ])->save();
    }
}
