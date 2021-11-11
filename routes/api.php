<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import your controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfilesController;

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
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);

Route::group(['middleware' => ['auth:api', 'forcePwdChg']], function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout')->withoutMiddleware('forcePwdChg');

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', function (Request $request) {
            return $request->user();
        })->withoutMiddleware('forcePwdChg');

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
});
