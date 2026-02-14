<?php

namespace App\Modules\Student\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Student\Contracts\IStudentService;
use App\Modules\Student\Contracts\IEnrollmentService;
use App\Modules\Student\Contracts\ITimetableService;
use App\Modules\Student\Services\StudentService;
use App\Modules\Student\Services\EnrollmentService;
use App\Modules\Student\Services\TimetableService;

class StudentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IStudentService::class, StudentService::class);
        $this->app->bind(IEnrollmentService::class, EnrollmentService::class);
        $this->app->bind(ITimetableService::class, TimetableService::class);

        $this->mergeConfigFrom(__DIR__ . '/../Config/student.php', 'student');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Route::middleware('api')->group(__DIR__ . '/../Routes/api.php');
    }
}
