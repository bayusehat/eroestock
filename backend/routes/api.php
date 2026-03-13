<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('users', UserController::class);
        Route::get('/roles/permissions/list', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class);

        Route::get('/accounts/tree', [AccountController::class, 'tree']);
        Route::apiResource('accounts', AccountController::class);

        Route::get('/settings', [CompanySettingController::class, 'index']);
        Route::put('/settings', [CompanySettingController::class, 'update']);
        Route::get('/settings/{key}', [CompanySettingController::class, 'show']);
        Route::put('/settings/{key}', [CompanySettingController::class, 'set']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
