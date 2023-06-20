<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Fortify;
use Laravel\Socialite\Contracts\User as Oauth;
use Laravel\Socialite\Facades\Socialite;

class AuthenticateLoginAttempt
{
    protected static function makeValidator(string $provider, Oauth $oauth): ?\Illuminate\Validation\Validator
    {
        $webexLoginValidator = function ($oauth) {
            $requiredRole = config('services.webex.require_role');
            $requiredRoleName = basename(base64_decode($requiredRole));
            $rolesMessage = "Sorry, your :attribute doesn't have the $requiredRoleName role.";
            $loginEnabledMessage = 'Sorry, your :attribute is disabled.';
            $attribute = "Webex account ({$oauth->getEmail()})";

            return Validator::make((array) $oauth, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'user.roles' => ['required', 'array', Rule::has($requiredRole)],
                'user.loginEnabled' => ['required', 'boolean', Rule::in(true)],
            ], [
                'user.roles' => $rolesMessage, 'user.loginEnabled' => $loginEnabledMessage,
            ], [
                'user.roles' => $attribute, 'user.loginEnabled' => $attribute,
            ]);
        };

        return match ($provider) {
            'webex' => $webexLoginValidator($oauth), default => null
        };
    }

    protected static function makeAccount(string $provider, Oauth $oauth, Carbon $now): ?Account
    {
        $webexAccountMaker = fn () => Account::make([
            'type' => 'oauth',
            'provider' => $provider,
            'provider_account_id' => $oauth->id,
            'scope' => $oauth->accessTokenResponseBody['scope'],
            'token_type' => $oauth->accessTokenResponseBody['token_type'],
            'access_token' => $oauth->accessTokenResponseBody['access_token'],
            'refresh_token' => $oauth->accessTokenResponseBody['refresh_token'],
            'expires_at' => $now->timestamp + $oauth->accessTokenResponseBody['expires_in'],
        ]);

        return match ($provider) {
            'webex' => $webexAccountMaker(),default => null
        };
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(Request $request, string $provider = 'webex'): ?Authenticatable
    {
        $webexLogin = function () use ($request, $provider) {
            $now = now();
            $oauth = Socialite::driver($provider)->user();

            $validator = self::makeValidator($provider, $oauth);
            $validator?->stopOnFirstFailure()->validate();

            $account = self::makeAccount($provider, $oauth, $now);
            $input = ['email' => $oauth->getEmail(), 'name' => $oauth->getName(), 'email_verified_at' => $now];

            return DB::transaction(function () use ($request, $input, $account) {
                $user = $this->upsertUser($input, $account);
                $request->session()->put(['account' => ['webex' => $account->id]]);

                return $user;
            }, 2);
        };

        if ($provider === 'webex') {
            return $webexLogin();
        } else {
            return $this->emailLogin($request);
        }
    }

    /**
     * Create or update a user record with it's associated account record using OAuth details.
     *
     * @throws \Throwable
     */
    protected function upsertUser(array $input, Account $account): Authenticatable
    {
        $existingUser = User::where('email', $input['email'])->first();

        if ($existingUser) {
            (new UpdateUserProfileInformation())->update($existingUser, $input, $account);

            return $existingUser->fresh();
        }

        $password = Str::password(10);
        $newUser = array_merge($input, [
            'password' => $password, 'password_confirmation' => $password,
        ]);

        return (new CreateNewUser())->create($newUser, $account);
    }

    /**
     * Username and password login flow.
     */
    protected function emailLogin(Request $request): ?Authenticatable
    {
        return Auth::guard(config('fortify.guard'))->attempt(
            $request->only(Fortify::username(), 'password'), $request->boolean('remember')
        )
            ? Auth::user()
            : null;
    }
}
