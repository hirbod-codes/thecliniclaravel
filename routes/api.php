<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

Route::middleware('throttle:global')->group(function () {
    Route::put('/updateLocale', function (UpdateLocaleRequest $request) {
        $locale = $request->safe()->only('locale');

        App::setLocale($locale);

        return response('The locale option successfully updated.', 200);
    });

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

    // Email verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware($authMiddleware)->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return response('', 200);
    })->middleware([$authMiddleware, 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return response('Verification link sent!', 200);
    })->middleware([$authMiddleware, 'throttle:1,1'])->name('verification.send');

    Route::middleware(['guest', 'throttle:6,1'])->post('/register/verifyPhonenumber', [AccountsController::class, 'verifyPhonenumber'])->name('auth.verifyPhonenumber');
    Route::middleware('guest')->post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::middleware($authMiddleware)->get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::middleware([$authMiddleware, 'throttle:api'])->group(function () use ($authMiddleware) {
        Route::controller(AccountsController::class)
            ->group(function () {
                Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name('accounts.index');

                Route::post('/accounts/verifyPhonenumber', 'verifyPhonenumber')->middleware('throttle:6,1')->name('accounts.verifyPhonenumber');
                Route::post('/accounts', 'store')->name('accounts.store');

                Route::get('/accounts/{accountId}', 'show')->name('accounts.show');
                Route::get('/accounts', 'showSelf')->name('accounts.showSelf');

                Route::put('/accounts/{accountId}', 'update')->name('accounts.update');
                Route::put('/accounts', 'updateSelf')->name('accounts.updateSelf');

                Route::delete('/accounts/{accountId}', 'destroy')->name('accounts.destroy');
                Route::delete('/accounts', 'destroySelf')->name('accounts.destroySelf');
            });

        Route::controller(RolesController::class)
            ->group(function () {
                Route::get('/roles', 'index')->name('roles.index');

                Route::get('/roles/{self?}/{accountId?}', 'show')->name('roles.show');

                Route::put('/roles', 'update')->name('roles.update');
            });

        Route::controller(OrdersController::class)
            ->group(function () {
                Route::get('/orders/Laser/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name('orders.laserIndex');
                Route::get('/orders/Regular/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name('orders.regularIndex');

                Route::post('/orders', 'store')->name('orders.store');

                Route::get('/orders/{businessName}/{accountId}/{orderId}', 'show')->name('orders.show');

                Route::delete('/orders/{businessName}/{accountId}/{orderId}', 'destroy')->name('orders.destroy');
            });

        Route::controller(VisitsController::class)
            ->group(function () {
                Route::get('/visits/Laser/{accountId}/{sortByTimestamp}/{laserOrderId?}/{timestamp?}', 'laserIndex')->name('visits.laserIndex');
                Route::get('/visits/Regular/{accountId}/{sortByTimestamp}/{regularOrderId?}/{timestamp?}', 'regularIndex')->name('visits.regularIndex');

                Route::post('/visits/regular', 'laserStore')->name('visits.laserStore');
                Route::post('/visits/laser', 'regularStore')->name('visits.regularStore');

                Route::get('/visits/Laser/{timestamp}', 'laserShow')->name('visits.laserShow');
                Route::get('/visits/Regular/{timestamp}', 'regularShow')->name('visits.regularShow');

                Route::delete('/visits/laser/{regularVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
                Route::delete('/visits/regular/{regularVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
            });
    });
});
