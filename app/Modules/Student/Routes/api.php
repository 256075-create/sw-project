<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Student\Http\Controllers\StudentController;
use App\Modules\Student\Http\Controllers\EnrollmentController;
use App\Modules\Student\Http\Controllers\TimetableController;

Route::prefix('api')->middleware('jwt.auth')->group(function () {
    // Student profile (for logged-in student — no extra permission needed)
    Route::get('/student/profile', [StudentController::class, 'profile']);

    // Students CRUD
    Route::middleware('permission:students.read')->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{studentId}', [StudentController::class, 'show']);
    });
    Route::middleware('permission:students.create')->post('/students', [StudentController::class, 'store']);
    Route::middleware('permission:students.update')->put('/students/{studentId}', [StudentController::class, 'update']);
    Route::middleware('permission:students.delete')->delete('/students/{studentId}', [StudentController::class, 'destroy']);

    // Enrollments
    Route::middleware('permission:enrollments.read')
        ->get('/students/{studentId}/enrollments', [EnrollmentController::class, 'index']);
    Route::middleware('permission:enrollments.enroll')
        ->post('/enrollments', [EnrollmentController::class, 'enroll']);
    Route::middleware('permission:enrollments.drop')
        ->post('/enrollments/{enrollmentId}/drop', [EnrollmentController::class, 'drop']);

    // Timetable
    Route::middleware('permission:timetable.view')->group(function () {
        Route::get('/students/{studentId}/timetable', [TimetableController::class, 'show']);
        Route::get('/students/{studentId}/timetable/{dayOfWeek}', [TimetableController::class, 'day']);
        Route::get('/students/{studentId}/timetable-export', [TimetableController::class, 'export']);
    });
});
