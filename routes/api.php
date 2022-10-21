<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\Privileges\ShowRequest;
use App\Models\BusinessDefault as ModelsBusinessDefault;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\DataStructures\User\DSPatient;
use App\Models\Auth\Patient;
use App\UseCases\Privileges\PrivilegesManagement;

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

    // Login
    Route::middleware('guest:api')->post('/login', [AuthController::class, 'apiLogin'])->name('api.auth.apiLogin');

    Route::middleware(['auth:api', 'phonenumber_verified'])->group(function () {
        Route::get('/isEmailVerified', function () {
            return response()->json(['verified' => (!(new CheckAuthentication)->getAuthenticated()->email_created_at ? true : false)]);
        });

        // Logout
        Route::middleware('auth:api')->get('/logout', [AuthController::class, 'apiLogout'])->name('api.auth.apiLogout');

        Route::controller(AccountsController::class)
            ->group(function () {
                Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name('api.accounts.index');

                // Phonenumber Verification Message Sender Route
                Route::post('/account/send-phoennumber-verification-code', 'sendPhonenumberVerificationCode')->name('api.account.sendPhonenumberVerificationCode');
                Route::post('/account/verify-phoennumber-verification-code', 'isPhonenumberVerificationCodeVerified')->name('api.auth.isPhonenumberVerificationCodeVerified');

                Route::get('/accountsCount/{roleName?}', 'accountsCount')->name('api.accounts.accountsCount');

                Route::post('/account/{roleName}', 'store')->name('api.account.store');

                Route::get('/account/{placeholder}', 'show')->name('api.account.show');
                Route::get('/account', 'showSelf')->name('api.account.showSelf');

                Route::put('/account/{accountId}', 'update')->name('api.account.update');
                Route::put('/account', 'updateSelf')->name('api.account.updateSelf');

                Route::delete('/account/{accountId}', 'destroy')->name('api.account.destroy');
                Route::delete('/account', 'destroySelf')->name('api.account.destroySelf');
            });

        Route::controller(RolesController::class)
            ->group(function () {
                Route::get('/dataType/{roleName?}', 'dataType')->name('api.roles.dataType');

                Route::get('/roles', 'index')->name('api.roles.index');

                Route::get('/role-name/{accountId?}', 'showRoleName')->name('api.role.showRoleName');
                Route::get('/role', 'show')->name('api.role.show');

                Route::post('/role', 'store')->name('api.role.store');

                Route::put('/role', 'update')->name('api.roles.update');

                Route::delete('/role/{roleName?}', 'destroy')->name('api.roles.destroy');
            });

        Route::controller(AccountDocumentsController::class)
            ->group(function () {
                Route::post('/avatar/{accountId?}', 'setAvatar')->name('api.document.setAvatar');

                Route::get('/avatar/{accountId?}', 'getAvatar')->name('api.document.getAvatar');
            });

        Route::controller(BusinessDefault::class)
            ->group(function () {
                Route::get('/settings', 'index')->name('api.document.index');

                Route::put('/setting', 'update')->name('api.document.update');
            });

        Route::controller(OrdersController::class)
            ->group(function () {
                Route::get('/orders/laser/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name('api.orders.laserIndex');
                Route::get('/orders/regular/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name('api.orders.regularIndex');

                Route::get('/ordersCount/{businessName?}/{roleName?}', 'ordersCount')->name('api.orders.ordersCount');

                Route::post('/order', 'store')->name('api.orders.store');

                Route::get('/orders/{businessName}/{accountId}/{orderId}', 'show')->name('api.orders.show');

                Route::delete('/orders/{businessName}/{accountId}/{orderId}', 'destroy')->name('api.orders.destroy');

                Route::get('/laser/parts/{gender?}', function (Request $request) {
                    $gender = $request->get('gender', null);
                    $validator = Validator::make($request->all(), ['gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender']]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors());
                    }

                    $dsParts = new DSParts(ucfirst(strtolower($gender)));
                    for ($i = 0; $i < count($parts = Part::query()->where('gender', '=', ucfirst(strtolower($gender)))->get()); $i++) {
                        $dsParts[] = $parts[$i]->getDSPart();
                    }

                    return $dsParts->toArray();
                })->name('api.orders.laser.parts');

                Route::get('/laser/packages/{gender?}', function (Request $request) {
                    $gender = $request->get('gender', null);
                    $validator = Validator::make($request->all(), ['gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender']]);
                    if ($validator->fails()) {
                        return response()->json($validator->errors());
                    }

                    $dsPackages = new DSPackages(ucfirst(strtolower($gender)));
                    for ($i = 0; $i < count($packages = Package::query()->where('gender', '=', ucfirst(strtolower($gender)))->with('parts')->get()); $i++) {
                        $dsPackages[] = $packages[$i]->getDSPackage();
                    }

                    return response()->json($dsPackages->toArray());
                })->name('api.orders.laser.packages');

                Route::post('/laser/time-calculation', 'calculateTime')->name('api.timeCalculation');
                Route::post('/laser/price-calculation', 'calculatePrice')->name('api.priceCalculation');
            });

        Route::controller(VisitsController::class)
            ->group(function () {
                Route::get('/visits/laser/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}', 'laserIndex')->name('api.visits.laserIndex');
                Route::get('/visits/regular/{accountId?}/{sortByTimestamp?}/{regularOrderId?}/{timestamp?}/{operator?}', 'regularIndex')->name('api.visits.regularIndex');

                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/laser', 'laserStore')->name('api.visits.laserStore');
                Route::middleware('adjustWeeklyTimePatterns')->post('/visit/regular', 'regularStore')->name('api.visits.regularStore');

                Route::get('/visit/laser/{timestamp}', 'laserShow')->name('api.visits.laserShow');
                Route::get('/visit/regular/{timestamp}', 'regularShow')->name('api.visits.regularShow');

                Route::post('/visit/laser/check', 'laserShowAvailable')->name('api.visits.laserShowAvailable');
                Route::post('/visit/regular/check', 'regularShowAvailable')->name('api.visits.regularShowAvailable');

                Route::delete('/visit/laser/{laserVisitId}', 'laserDestroy')->name('api.visits.laserDestroy');
                Route::delete('/visit/regular/{regularVisitId}', 'regularDestroy')->name('api.visits.regularDestroy');
            });
    });
});
