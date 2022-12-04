<?php

namespace Routes;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Models\Package\Package;
use App\Models\Part\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class CommonRoutes
{
    public function callCommonRoutes(string $from): void
    {
        Route::controller(AccountsController::class)
            ->group(function () use ($from) {
                if ($from === 'web') {
                    Route::get('/dashboard/account', function () {
                        return view('app');
                    })->name($from . '.account.page');
                }

                Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name($from . '.accounts.index');

                Route::get('/accountsCount/{roleName?}', 'accountsCount')->name($from . '.accounts.accountsCount');

                Route::post('/account/admin/{roleName}', 'storeAdmin')->name($from . '.account.storeAdmin');
                Route::post('/account/doctor/{roleName}', 'storeDoctor')->name($from . '.account.storeDoctor');
                Route::post('/account/secretary/{roleName}', 'storeSecretary')->name($from . '.account.storeSecretary');
                Route::post('/account/operator/{roleName}', 'storeOperator')->name($from . '.account.storeOperator');
                Route::post('/account/patient/{roleName}', 'storePatient')->name($from . '.account.storePatient');

                Route::get('/account/{placeholder}', 'show')->name($from . '.account.show');
                Route::get('/account', 'showSelf')->name($from . '.account.showSelf');

                Route::post('/account/{accountId}', 'update')->name($from . '.account.update');

                Route::delete('/account/{accountId}', 'destroy')->name($from . '.account.destroy');
            });

        Route::controller(RolesController::class)
            ->group(function () use ($from) {
                Route::get('/dataType/{roleName?}', 'dataType')->name($from . '.roles.dataType');

                Route::get('/roles', 'index')->name($from . '.roles.index');

                Route::post('/role', 'store')->name($from . '.role.store');

                Route::get('/role-name/{accountId?}', 'showRoleName')->name($from . '.role.showRoleName');
                Route::get('/role', 'show')->name($from . '.role.show');

                Route::put('/role', 'update')->name($from . '.roles.update');

                Route::delete('/role/{roleName?}', 'destroy')->name($from . '.roles.destroy');
            });

        Route::controller(AccountDocumentsController::class)
            ->group(function () use ($from) {
                Route::post('/avatar/{accountId?}', 'setAvatar')->name($from . '.document.setAvatar');

                Route::get('/avatar/{accountId?}', 'getAvatar')->name($from . '.document.getAvatar');
            });

        Route::controller(BusinessDefault::class)
            ->group(function () use ($from) {
                Route::get('/settings', 'index')->name($from . '.document.index');

                Route::put('/setting', 'update')->name($from . '.document.update');
            });

        Route::controller(OrdersController::class)
            ->group(function () use ($from) {
                if ($from === 'web') {
                    Route::get('/dashboard/order', function () {
                        return view('app');
                    })->name($from . '.order.page');
                }

                Route::get('/orders/laser/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name($from . '.orders.laserIndex');
                Route::get('/orders/regular/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name($from . '.orders.regularIndex');

                Route::get('/ordersCount/{businessName?}/{roleName?}', 'ordersCount')->name($from . '.orders.ordersCount');

                Route::post('/order', 'store')->name($from . '.orders.store');

                Route::delete('/order/{businessName}/{childOrderId}', 'destroy')->name($from . '.orders.destroy');

                Route::get('/laser/parts/{gender?}', function (Request $request) {
                    $validator = Validator::make($request->all(), ['gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender']]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors());
                    }
                    $gender = $request->get('gender', null);

                    $dsParts = new DSParts(ucfirst(strtolower($gender)));
                    for ($i = 0; $i < count($parts = Part::query()->where('gender', '=', ucfirst(strtolower($gender)))->get()); $i++) {
                        $dsParts[] = $parts[$i]->getDSPart();
                    }

                    return $dsParts->toArray();
                })->name($from . '.orders.laser.parts');

                Route::get('/laser/packages/{gender?}', function (Request $request) {
                    $validator = Validator::make($request->all(), ['gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender']]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors());
                    }
                    $gender = $request->get('gender', null);

                    $dsPackages = new DSPackages(ucfirst(strtolower($gender)));
                    for ($i = 0; $i < count($packages = Package::query()->where('gender', '=', ucfirst(strtolower($gender)))->with('parts')->get()); $i++) {
                        $dsPackages[] = $packages[$i]->getDSPackage();
                    }

                    return response()->json($dsPackages->toArray());
                })->name($from . '.orders.laser.packages');

                Route::post('/laser/time-calculation', 'calculateTime')->name($from . '.timeCalculation');
                Route::post('/laser/price-calculation', 'calculatePrice')->name($from . '.priceCalculation');
            });

        Route::controller(VisitsController::class)
            ->group(function () use ($from) {
                if ($from === 'web') {
                    Route::get('/dashboard/visit', function () {
                        return view('app');
                    })->name($from . '.visit.page');
                }

                Route::get('/visits/{businessName?}/{roleName?}/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}/{count?}/{lastVisitTimestamp?}', 'index')->name($from . '.visits.index');
                Route::get('/visitsCount/{businessName?}/{roleName?}', 'visitsCount')->name($from . '.visits.visitsCount');

                Route::post('/visit/laser', 'laserStore')->name($from . '.visits.laserStore');
                Route::post('/visit/regular', 'regularStore')->name($from . '.visits.regularStore');

                Route::post('/visit/laser/check', 'laserShowAvailable')->name($from . '.visits.laserShowAvailable');
                Route::post('/visit/regular/check', 'regularShowAvailable')->name($from . '.visits.regularShowAvailable');

                Route::delete('/visit/laser/{visitId}', 'laserDestroy')->name($from . '.visits.laserDestroy');
                Route::delete('/visit/regular/{visitId}', 'regularDestroy')->name($from . '.visits.regularDestroy');
            });
    }
}
