<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\Search\ElasticsearchService;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ElasticsearchService::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(function () {
                Route::middleware('jwt.auth')->group(function () {
                    Route::get('/search/students', [\App\Http\Controllers\Api\SearchController::class, 'searchStudents'])
                        ->middleware('permission:students.read');
                    Route::get('/search/courses', [\App\Http\Controllers\Api\SearchController::class, 'searchCourses'])
                        ->middleware('permission:courses.read');
                });
            });
    }
}
