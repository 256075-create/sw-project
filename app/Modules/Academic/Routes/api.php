<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Academic\Http\Controllers\UniversityController;
use App\Modules\Academic\Http\Controllers\CollegeController;
use App\Modules\Academic\Http\Controllers\DepartmentController;
use App\Modules\Academic\Http\Controllers\MajorController;

Route::prefix('api')->middleware('jwt.auth')->group(function () {
    // Hierarchy view
    Route::get('/academic/hierarchy', [UniversityController::class, 'hierarchy'])
        ->middleware('permission:academic.read');

    // Universities
    Route::middleware('permission:academic.read')->group(function () {
        Route::get('/universities', [UniversityController::class, 'index']);
        Route::get('/universities/{universityId}', [UniversityController::class, 'show']);
    });
    Route::middleware('permission:academic.create')->post('/universities', [UniversityController::class, 'store']);
    Route::middleware('permission:academic.update')->put('/universities/{universityId}', [UniversityController::class, 'update']);
    Route::middleware('permission:academic.delete')->delete('/universities/{universityId}', [UniversityController::class, 'destroy']);

    // Colleges
    Route::middleware('permission:academic.read')->group(function () {
        Route::get('/colleges', [CollegeController::class, 'index']);
        Route::get('/colleges/{collegeId}', [CollegeController::class, 'show']);
    });
    Route::middleware('permission:academic.create')->post('/colleges', [CollegeController::class, 'store']);
    Route::middleware('permission:academic.update')->put('/colleges/{collegeId}', [CollegeController::class, 'update']);
    Route::middleware('permission:academic.delete')->delete('/colleges/{collegeId}', [CollegeController::class, 'destroy']);

    // Departments
    Route::middleware('permission:academic.read')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::get('/departments/{departmentId}', [DepartmentController::class, 'show']);
    });
    Route::middleware('permission:academic.create')->post('/departments', [DepartmentController::class, 'store']);
    Route::middleware('permission:academic.update')->put('/departments/{departmentId}', [DepartmentController::class, 'update']);
    Route::middleware('permission:academic.delete')->delete('/departments/{departmentId}', [DepartmentController::class, 'destroy']);

    // Majors
    Route::middleware('permission:academic.read')->group(function () {
        Route::get('/majors', [MajorController::class, 'index']);
        Route::get('/majors/{majorId}', [MajorController::class, 'show']);
    });
    Route::middleware('permission:academic.create')->post('/majors', [MajorController::class, 'store']);
    Route::middleware('permission:academic.update')->put('/majors/{majorId}', [MajorController::class, 'update']);
    Route::middleware('permission:academic.delete')->delete('/majors/{majorId}', [MajorController::class, 'destroy']);
});
