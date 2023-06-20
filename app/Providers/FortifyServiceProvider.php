<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateLoginAttempt;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    protected function configureRoutes(string $path): void
    {
        Route::group([
            'namespace' => 'Laravel\Fortify\Http\Controllers',
            'domain' => config('fortify.domain'),
            'prefix' => config('fortify.prefix'),
        ], function () use ($path) {
            $this->loadRoutesFrom($path);
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRoutes(base_path('/routes/fortify.php'));

        Fortify::authenticateUsing([new AuthenticateLoginAttempt, '__invoke']);
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', fn(Request $request) => Limit::perMinute(5)
            ->by(((string) $request->email).$request->ip()));

        RateLimiter::for('two-factor', fn(Request $request) => Limit::perMinute(5)
            ->by($request->session()->get('login.id')));
    }
}
