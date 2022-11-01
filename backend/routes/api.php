<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Models\BusinessDefault as ModelsBusinessDefault;
use App\Models\Package\Package;
use App\Models\Part\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;

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

Route::prefix('{locale}')->group(function () {
    Route::get('/isAuthenticated', function () {
        return response()->json(['authenticated' => !(new CheckAuthentication)->checkIfThereIsNoAuthenticated()]);
    });

    Route::get('/locales', function () {
        $locales = [];
        foreach ($dirs = scandir(base_path() . '/lang') as $value) {
            if (in_array($value, ['.', '..']) || !is_dir(base_path() . '/lang/' . $value)) {
                continue;
            }

            $longName = include(base_path() . '/lang/' . $value . '/language_name.php');

            $locales[] = ['longName' => $longName, 'shortName' => $value, 'direction' => (include(base_path() . '/lang/' . $value . '/direction.php'))];
        }

        return response()->json($locales);
    });

    Route::get('/genders', function () {
        return response()->json(ModelsBusinessDefault::firstOrFail()->genders);
    });

    Route::get('/states', function () {
        return response()->json(
            array_map(function (array $state) {
                return $state['name'];
            }, json_decode(Storage::disk('public')->get('states.json'), true))
        );
    });

    Route::get('/cities/{stateName?}', function (Request $request) {
        if (!is_string($stateName = $request->get('stateName', ''))) {
            throw new \TypeError('only string type is acceptable for name query paraemter.', 500);
        }

        foreach (json_decode(Storage::disk('public')->get('states.json'), true) as $state) {
            if ($state['name'] !== $stateName) {
                continue;
            }

            $id = $state['id'];
        }

        if (!isset($id)) {
            return response()->json(['error' => 'Provided state has not been found.'], 404);
        }

        $cities = [];
        foreach (json_decode(Storage::disk('public')->get('cities.json'), true) as $city) {
            if ($city['province_id'] === $id) {
                $cities[] = $city['name'];
            }
        }

        return response()->json($cities);
    });

    Route::get('/work-schedule', function () {
        return response()->json((ModelsBusinessDefault::query()->firstOrFail()->work_schedule)->toArray());
    })->name('workSchedule');

    Route::middleware('guest')->group(function () {
        Route::post('/login', [AuthController::class, 'apiLogin'])->name('auth.apiLogin');
    });

    Route::middleware(['auth', 'phonenumber_verified'])->group(function () {
        Route::get('/logout', [AuthController::class, 'apiLogout'])->name('auth.apiLogout');

        Route::controller(AccountsController::class)
            ->group(function () {
                Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name('accounts.index');

                Route::get('/accountsCount/{roleName?}', 'accountsCount')->name('accounts.accountsCount');

                Route::post('/account/admin/{roleName}', 'storeAdmin')->name('account.storeAdmin');
                Route::post('/account/doctor/{roleName}', 'storeDoctor')->name('account.storeDoctor');
                Route::post('/account/secretary/{roleName}', 'storeSecretary')->name('account.storeSecretary');
                Route::post('/account/operator/{roleName}', 'storeOperator')->name('account.storeOperator');
                Route::post('/account/patient/{roleName}', 'storePatient')->name('account.storePatient');

                Route::get('/account/{placeholder}', 'show')->name('account.show');
                Route::get('/account', 'showSelf')->name('account.showSelf');

                Route::put('/account/{accountId}', 'update')->name('account.update');

                Route::delete('/account/{accountId}', 'destroy')->name('account.destroy');
            });

        Route::controller(RolesController::class)
            ->group(function () {
                Route::get('/dataType/{roleName?}', 'dataType')->name('roles.dataType');

                Route::get('/roles', 'index')->name('roles.index');

                Route::post('/role', 'store')->name('role.store');

                Route::get('/role-name/{accountId?}', 'showRoleName')->name('role.showRoleName');
                Route::get('/role', 'show')->name('role.show');

                Route::put('/role', 'update')->name('roles.update');

                Route::delete('/role/{roleName?}', 'destroy')->name('roles.destroy');
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
                Route::get('/orders/laser/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name('orders.laserIndex');
                Route::get('/orders/regular/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name('orders.regularIndex');

                Route::get('/ordersCount/{businessName?}/{roleName?}', 'ordersCount')->name('orders.ordersCount');

                Route::post('/order', 'store')->name('orders.store');

                Route::delete('/order/{businessName}/{childOrderId}', 'destroy')->name('orders.destroy');

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
                })->name('orders.laser.parts');

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
                })->name('orders.laser.packages');

                Route::post('/laser/time-calculation', 'calculateTime')->name('timeCalculation');
                Route::post('/laser/price-calculation', 'calculatePrice')->name('priceCalculation');
            });

        Route::controller(VisitsController::class)
            ->group(function () {
                Route::get('/visits/{businessName?}/{roleName?}/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}/{count?}/{lastVisitTimestamp?}', 'index')->name('visits.index');
                Route::get('/visitsCount/{businessName?}/{roleName?}', 'visitsCount')->name('visits.visitsCount');

                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/laser', 'laserStore')->name('visits.laserStore');
                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/regular', 'regularStore')->name('visits.regularStore');

                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/laser/check', 'laserShowAvailable')->name('visits.laserShowAvailable');
                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/regular/check', 'regularShowAvailable')->name('visits.regularShowAvailable');

                Route::delete('/visit/laser/{visitId}', 'laserDestroy')->name('visits.laserDestroy');
                Route::delete('/visit/regular/{visitId}', 'regularDestroy')->name('visits.regularDestroy');
            });
    });
});
