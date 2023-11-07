<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\User;
use App\Traits\PasswordRules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function create(array $input, Account $account = null): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'email_verified_at' => ['sometimes'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
        $inputValidator = Validator::make($input, $rules);
        $validatedInput = $inputValidator->validate();
        $user = User::make([
            'name' => $validatedInput['name'],
            'email' => $validatedInput['email'],
            'password' => Hash::make($validatedInput['password']),
            'email_verified_at' => $account ? $validatedInput['email_verified_at'] : null,
        ]);

        $user->save();
        $account?->user()->associate($user)->save();

        return $user;
    }
}
