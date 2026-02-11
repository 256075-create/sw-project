<?php

namespace App\Modules\Academic\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Services\AcademicStructureService;

class AcademicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IAcademicStructureService::class, AcademicStructureService::class);

        $this->mergeConfigFrom(__DIR__ . '/../Config/academic.php', 'academic');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::middleware('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
