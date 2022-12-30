<?php

use App\Http\Controllers\AuditLogsController;
// Import your controllers
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\PasswordPoliciesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('refreshToken', [LoginController::class, 'refreshToken']);
Route::post('password/firstTime', [ProfilesController::class, 'firstTimeLogin']);
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth:api', 'forcePwdChg']], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [ProfilesController::class, 'me'])->withoutMiddleware('forcePwdChg');
        Route::post('changePassword', [ProfilesController::class, 'changePassword'])->withoutMiddleware('forcePwdChg');
    });

    Route::group(['prefix' => 'permissions'], function () {
        Route::get('/', [PermissionsController::class, 'index']);
        Route::post('/', [PermissionsController::class, 'store']);
        Route::delete('{id}', [PermissionsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RolesController::class, 'index']);
        Route::post('/', [RolesController::class, 'store']);
        Route::get('{id}', [RolesController::class, 'show']);
        Route::put('{id}', [RolesController::class, 'update']);
        Route::delete('{id}', [RolesController::class, 'destroy']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::post('/', [UsersController::class, 'store']);
        Route::get('{id}', [UsersController::class, 'show']);
        Route::put('{id}', [UsersController::class, 'update']);
        Route::delete('{id}', [UsersController::class, 'destroy']);
    });

    Route::group(['prefix' => 'options'], function () {
        Route::get('/', [OptionsController::class, 'index']);
        Route::post('/', [OptionsController::class, 'store']);
        Route::get('detailed', [OptionsController::class, 'detailedList']);
        Route::get('categories', [OptionsController::class, 'categoryList']);
        Route::get('{id}', [OptionsController::class, 'show']);
        Route::put('{id}', [OptionsController::class, 'update']);
        Route::delete('{id}', [OptionsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'pwdPolicies'], function () {
        Route::get('/', [PasswordPoliciesController::class, 'index']);
        Route::get('{id}', [PasswordPoliciesController::class, 'show']);
        Route::put('{id}', [PasswordPoliciesController::class, 'update']);
    });

    Route::group(['prefix' => 'auditLogs'], function () {
        Route::get('/', [AuditLogsController::class, 'index']);
        Route::post('search', [AuditLogsController::class, 'search']);
        Route::get('{id}', [AuditLogsController::class, 'show']);
    });
});
