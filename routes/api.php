<?php

use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\UpdateLocaleRequest;
use App\Models\BusinessDefault as ModelsBusinessDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

Route::get('genders', function () {
    return response()->json(ModelsBusinessDefault::firstOrFail()->genders);
});

Route::get('states', function () {
    return response()->json(
        array_map(function (array $state) {
            return $state['name'];
        }, json_decode(Storage::disk('public')->get('states.json'), true))
    );
});

Route::get('cities/{stateName?}', function (Request $request) {
    if (!is_string($stateName = $request->get('stateName', ''))) {
        throw new \TypeError('only string type is acceptable for name query paraemter.', 500);
    }

    foreach (json_decode(Storage::disk('public')->get('states.json'), true) as $state) {
        if ($state['name'] !== $stateName) {
            continue;
        }

        $id = $state['id'];
    }

    $cities = [];
    foreach (json_decode(Storage::disk('public')->get('cities.json'), true) as $city) {
        if ($city['province_id'] === $id) {
            $cities[] = $city['name'];
        }
    }

    return response()->json($cities);
});

Route::put('/updateLocale', function (UpdateLocaleRequest $request) {
    $locale = $request->safe()->only('locale');

    App::setLocale($locale['locale']);

    return response('The locale option successfully updated.', 200);
});

// Login
Route::middleware('guest:api')->post('/login', [AuthController::class, 'apiLogin'])->name('auth.apiLogin');

Route::middleware(['auth:api', 'phonenumber_verified'])->group(function () {
    // Logout
    Route::middleware('auth:api')->get('/logout', [AuthController::class, 'apiLogout'])->name('auth.apiLogout');

    Route::controller(AccountsController::class)
        ->group(function () {
            Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name('accounts.index');

            // Phonenumber Verification Message Sender Route
            Route::post('/account/send-phoennumber-verification-code', 'sendPhonenumberVerificationCode')->name('account.sendPhonenumberVerificationCode');

            Route::post('/account/{roleName}', 'store')->name('account.store');

            Route::get('/account/{username}', 'show')->name('account.show');
            Route::get('/account', 'showSelf')->name('account.showSelf');

            Route::put('/account/{accountId}', 'update')->name('account.update');
            Route::put('/account', 'updateSelf')->name('account.updateSelf');

            Route::delete('/account/{accountId}', 'destroy')->name('account.destroy');
            Route::delete('/account', 'destroySelf')->name('account.destroySelf');
        });

    Route::controller(RolesController::class)
        ->group(function () {
            Route::get('/roles', 'index')->name('roles.index');

            Route::post('/role', 'store')->name('roles.store');

            Route::get('/role/{self?}/{accountId?}', 'show')->name('roles.show');

            Route::put('/role', 'update')->name('roles.update');
        });

    Route::controller(AccountDocumentsController::class)
        ->group(function () {
            Route::post('/avatar/{accountId?}', 'setAvatar')->name('document.setAvatar');

            Route::get('/avatar/{accountId?}', 'getAvatar')->name('document.getAvatar');
        });

    Route::controller(BusinessDefault::class)
        ->group(function () {
            Route::get('/settings', 'index')->name('document.index');

            Route::put('/setting', 'update')->name('document.update');
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
            Route::get('/visits/laser/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}', 'laserIndex')->name('visits.laserIndex');
            Route::get('/visits/regular/{accountId?}/{sortByTimestamp?}/{regularOrderId?}/{timestamp?}/{operator?}', 'regularIndex')->name('visits.regularIndex');

            Route::post('/visit/laser', 'laserStore')->name('visits.laserStore');
            Route::post('/visit/regular', 'regularStore')->name('visits.regularStore');

            Route::get('/visit/laser/{timestamp}', 'laserShow')->name('visits.laserShow');
            Route::get('/visit/regular/{timestamp}', 'regularShow')->name('visits.regularShow');

            Route::delete('/visit/laser/{laserVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
            Route::delete('/visit/regular/{regularVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
        });
});
