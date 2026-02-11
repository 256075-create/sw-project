<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Registration\Http\Controllers\CourseController;
use App\Modules\Registration\Http\Controllers\SectionController;
use App\Modules\Registration\Http\Controllers\ClassroomController;
use App\Modules\Registration\Http\Controllers\ScheduleController;

Route::prefix('api')->middleware('jwt.auth')->group(function () {
    // Courses
    Route::middleware('permission:courses.read')->group(function () {
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{courseId}', [CourseController::class, 'show']);
    });
    Route::middleware('permission:courses.create')->post('/courses', [CourseController::class, 'store']);
    Route::middleware('permission:courses.update')->group(function () {
        Route::put('/courses/{courseId}', [CourseController::class, 'update']);
        Route::post('/courses/{courseId}/activate', [CourseController::class, 'activate']);
        Route::post('/courses/{courseId}/deactivate', [CourseController::class, 'deactivate']);
    });
    Route::middleware('permission:courses.delete')->delete('/courses/{courseId}', [CourseController::class, 'destroy']);

    // Sections
    Route::middleware('permission:sections.read')->group(function () {
        Route::get('/sections', [SectionController::class, 'index']);
        Route::get('/sections/{sectionId}', [SectionController::class, 'show']);
    });
    Route::middleware('permission:sections.create')->post('/sections', [SectionController::class, 'store']);
    Route::middleware('permission:sections.update')->put('/sections/{sectionId}', [SectionController::class, 'update']);
    Route::middleware('permission:sections.delete')->delete('/sections/{sectionId}', [SectionController::class, 'destroy']);

    // Classrooms
    Route::middleware('permission:classrooms.read')->group(function () {
        Route::get('/classrooms', [ClassroomController::class, 'index']);
        Route::get('/classrooms/{classroomId}', [ClassroomController::class, 'show']);
    });
    Route::middleware('permission:classrooms.create')->post('/classrooms', [ClassroomController::class, 'store']);
    Route::middleware('permission:classrooms.update')->put('/classrooms/{classroomId}', [ClassroomController::class, 'update']);
    Route::middleware('permission:classrooms.delete')->delete('/classrooms/{classroomId}', [ClassroomController::class, 'destroy']);

    // Schedules
    Route::middleware('permission:sections.read')->group(function () {
        Route::get('/sections/{sectionId}/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/{scheduleId}', [ScheduleController::class, 'show']);
    });
    Route::middleware('permission:sections.create')->post('/schedules', [ScheduleController::class, 'store']);
    Route::middleware('permission:sections.update')->put('/schedules/{scheduleId}', [ScheduleController::class, 'update']);
    Route::middleware('permission:sections.delete')->delete('/schedules/{scheduleId}', [ScheduleController::class, 'destroy']);
});
