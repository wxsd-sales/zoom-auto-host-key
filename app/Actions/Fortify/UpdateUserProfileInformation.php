<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function update(User $user, array $input, Account $account = null): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
        $inputValidator = Validator::make($input, $rules);
        $validatedInput = $inputValidator->validateWithBag('updateProfileInformation');

        if (isset($validatedInput['photo'])) {
            $user->updateProfilePhoto($validatedInput['photo']);
        }

        $account?->user()->associate($user)->save();

        if ($validatedInput['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $validatedInput['name'],
                'email' => $validatedInput['email'],
                'email_verified_at' => $account ? $input['email_verified_at'] : null,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill(['name' => $input['name'], 'email' => $input['email'], 'email_verified_at' => null])->save();

        $user->sendEmailVerificationNotification();
    }
}
