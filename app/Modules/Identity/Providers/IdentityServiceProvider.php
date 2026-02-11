<?php

namespace App\Modules\Identity\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Identity\Contracts\IAuthenticationService;
use App\Modules\Identity\Contracts\IAuthorizationService;
use App\Modules\Identity\Contracts\IUserService;
use App\Modules\Identity\Services\AuthenticationService;
use App\Modules\Identity\Services\AuthorizationService;
use App\Modules\Identity\Services\UserService;
use App\Modules\Identity\Events\UserLoggedIn;
use App\Modules\Identity\Events\UserLoggedOut;
use App\Modules\Identity\Listeners\UpdateLastLogin;
use App\Modules\Identity\Listeners\LogUserLogout;
use App\Modules\Identity\Services\ElasticsearchService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IAuthenticationService::class, AuthenticationService::class);
        $this->app->bind(IAuthorizationService::class, AuthorizationService::class);
        $this->app->bind(IUserService::class, UserService::class);
        $this->app->singleton(ElasticsearchService::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/identity.php',
            'identity'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::middleware('api')->group(
            __DIR__ . '/../Routes/api.php'
        );

        // Register middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', \App\Modules\Identity\Http\Middleware\AuthenticateJWT::class);
        $router->aliasMiddleware('permission', \App\Modules\Identity\Http\Middleware\CheckPermission::class);

        // Register event listeners
        Event::listen(UserLoggedIn::class, UpdateLastLogin::class);
        Event::listen(UserLoggedOut::class, LogUserLogout::class);
    }
}
