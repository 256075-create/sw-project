<?php

namespace App\Modules\Registration\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Registration\Contracts\ICourseService;
use App\Modules\Registration\Contracts\ISectionService;
use App\Modules\Registration\Services\CourseService;
use App\Modules\Registration\Services\SectionService;

class RegistrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ICourseService::class, CourseService::class);
        $this->app->bind(ISectionService::class, SectionService::class);

        $this->mergeConfigFrom(__DIR__ . '/../Config/registration.php', 'registration');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::middleware('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
