<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
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

Route::group(['middleware' => ['web', 'auth']], function ($router) {
    $router->get('/clients', function () {
    });

    $router->post('/clients', function () {
    });

    $router->put('/clients/{client_id}', function () {
    });

    $router->delete('/clients/{client_id}', function () {
    });
});

$authMiddleware = 'auth:' . implode(', ', array_keys(app()['config']['auth.guards']));

Route::controller(AuthController::class)->group(function () use ($authMiddleware) {
    Route::middleware('guest')->put('/register', 'register')->name('auth.register');

    Route::middleware($authMiddleware)->get('/logout', 'logout')->name('auth.logout');
});

Route::controller(AccountsController::class)
    ->middleware($authMiddleware)
    ->group(function () {
        Route::get('/accounts/{ruleName}/{count?}/{lastAccountId?}', 'index')->name('accounts.index');

        // Route::get('/accounts/create', 'create')->name('accounts.create');

        Route::post('/accounts', 'store')->name('accounts.store');

        Route::get('/accounts/{accountId}', 'show')->name('accounts.show');

        // Route::get('/accounts/{accountId}/edit', 'edit')->name('accounts.edit');

        Route::put('/accounts/{accountId}', 'update')->name('accounts.update');

        Route::delete('/accounts/{accountId}', 'destroy')->name('accounts.destroy');
    });

Route::controller(RolesController::class)
    ->middleware($authMiddleware)
    ->group(function () {
        Route::get('/roles', 'index')->name('roles.index');

        Route::post('/roles', 'store')->name('roles.store');

        Route::get('/roles/{self?}/{accountId?}', 'show')->name('roles.show');
    });

Route::middleware($authMiddleware)->get('privileges', function () {
    return response()->json((new CheckAuthentication)->getAuthenticatedDSUser()::getUserPrivileges());
});
