<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Identity\Http\Controllers\AuthController;
use App\Modules\Identity\Http\Controllers\UserController;
use App\Modules\Identity\Http\Controllers\RoleController;

// Public auth routes
Route::prefix('api/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Authenticated routes
Route::prefix('api')->middleware('jwt.auth')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Current user
    Route::get('/me', [UserController::class, 'me']);

    // User management (Admin only)
    Route::middleware('permission:users.read')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{userId}', [UserController::class, 'show']);
    });

    Route::middleware('permission:users.create')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });

    Route::middleware('permission:users.update')->group(function () {
        Route::put('/users/{userId}', [UserController::class, 'update']);
        Route::post('/users/{userId}/roles', [UserController::class, 'assignRole']);
        Route::delete('/users/{userId}/roles/{roleId}', [UserController::class, 'removeRole']);
    });

    // Role management (Admin only)
    Route::middleware('permission:roles.read')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{roleId}', [RoleController::class, 'show']);
    });

    Route::middleware('permission:roles.create')->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
    });

    Route::middleware('permission:roles.update')->group(function () {
        Route::put('/roles/{roleId}', [RoleController::class, 'update']);
        Route::post('/roles/{roleId}/permissions', [RoleController::class, 'assignPermission']);
        Route::delete('/roles/{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission']);
    });

    Route::middleware('permission:roles.delete')->group(function () {
        Route::delete('/roles/{roleId}', [RoleController::class, 'destroy']);
    });
});
