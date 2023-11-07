<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Traits\PasswordRules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ];
        $messages = [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ];
        $inputValidator = Validator::make($input, $rules, $messages);
        $validatedInput = $inputValidator->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($validatedInput['password']),
        ])->save();
    }
}
