<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\Roles\ShowRequest;
use App\Http\Requests\UpdateLocaleRequest;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use TheClinicUseCases\Privileges\PrivilegesManagement;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/isAuthenticated', function () {
    return response()->json(['authenticated' => !(new CheckAuthentication)->checkIfThereIsNoAuthenticated()]);
});

Route::get('/theme', function () {
    return response()->json(['theme' => session()->get('theme', 'light')]);
});

Route::post('/theme', function (Request $request) {
    $validator = Validator::make($request->all(), ['theme' => ['required', 'string', 'max:125']]);
    if ($validator->fails()) {
        return response()->json();
    }

    $request->session()->put('theme', $request->theme);

    return response()->json(['message' => trans_choice('general.theme-store-success', 0)]);
});

Route::get('/locale', function () {
    $locale = session()->get('locale', App::getLocale());

    $direction = include(base_path() . '/lang/' . $locale . '/direction.php');
    $longName = include(base_path() . '/lang/' . $locale . '/language_name.php');

    return response()->json(['longName' => $longName, 'shortName' => $locale, 'direction' => $direction]);
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

Route::put('/locale', function (UpdateLocaleRequest $request) {
    $validatedInput = $request->safe()->only('locale');

    foreach (scandir(base_path() . '/lang') as $dir) {
        if (in_array($dir, ['..', '.']) || !is_dir(base_path() . '/lang/' . $dir)) {
            continue;
        }

        $longName = include(base_path() . '/lang/' . $dir . '/language_name.php');

        if ($validatedInput['locale'] === $dir || $validatedInput['locale'] === $longName) {
            session()->put('locale', $dir);
            App::setLocale($dir);
            return response(trans_choice('general.set-locale-success', 0));
        }
    }

    return response(trans_choice('general.set-locale-failure', 0), 422);
});

Route::get('/', function () {
    return view('app');
})->name('home');

Route::middleware('guest:web')->group(function () {
    // Register
    Route::get('/register/{redirecturl?}', function (Request $request) {
        if (!is_null($redirecturl = $request->get('redirecturl'))) {
            $request->session()->put('redirecturl', $redirecturl);
        }
        return view('app');
    })->name('auth.register.page');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    // Login
    Route::get('/login', fn () => view('app'))->name('auth.login.page');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Verify Phonenumber
    Route::post('/register/send-phoennumber-verification-code', [AuthController::class, 'sendPhonenumberVerificationCode'])->name('auth.sendPhonenumberVerificationCode');
    Route::post('/register/verify-phoennumber-verification-code', [AuthController::class, 'verifyPhonenumberVerificationCode'])->name('auth.verifyPhonenumberVerificationCode');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot_password');

    Route::put('/reset-password', [AuthController::class, 'resetPassword'])->name('reset_password');
});

Route::middleware('auth:web')->group(function () {
    Route::get('/isEmailVerified', function () {
        $user = (new CheckAuthentication)->getAuthenticated();
        return response()->json(['verified' => ($user->email ? ($user->email_verified_at ? true : false) : false)]);
    });

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        $session = $request->session();
        $redirect = $session->get('redirecturl');
        $session->forget('redirecturl');

        return redirect($redirect ?: '/');
    })->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/');
    })->middleware('signed')->name('verification.verify');

    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::middleware('phonenumber_verified')->group(function () {
        Route::controller(AccountsController::class)
            ->group(function () {
                Route::get('/accounts/{roleName?}/{count?}/{lastAccountId?}', 'index')->name('accounts.index');

                // Phonenumber Verification Message Sender Route
                Route::post('/account/send-phoennumber-verification-code', 'sendPhonenumberVerificationCode')->name('account.sendPhonenumberVerificationCode');

                Route::post('/account/{roleName}', 'store')->name('account.store');

                Route::get('/account/{placeholder}', 'show')->name('account.show');
                Route::get('/account', 'showSelf')->name('account.showSelf');

                Route::put('/account/{accountId}', 'update')->name('account.update');
                Route::put('/account', 'updateSelf')->name('account.updateSelf');

                Route::delete('/account/{accountId}', 'destroy')->name('account.destroy');
                Route::delete('/account', 'destroySelf')->name('account.destroySelf');
            });

        Route::controller(RolesController::class)
            ->group(function () {
                Route::get('/roles', 'index')->name('roles.index');
                Route::get('/privileges', function () {
                    $authenticated = (new CheckAuthentication)->getAuthenticatedDSUser();

                    return response()->json((new PrivilegesManagement)->getPrivileges($authenticated));
                })->name('privileges.index');

                Route::post('/role', 'store')->name('role.store');

                Route::get('/privilege/{roleName?}', function (ShowRequest $request) {
                    $validateInput = $request->safe()->all();

                    $privileges = [];
                    DB::table((new Role)->getTable())
                        ->select([(new Privilege)->getTable() . '.name', (new PrivilegeValue)->getTable() . '.privilegeValue'])
                        ->join((new PrivilegeValue)->getTable(), (new PrivilegeValue)->getTable() . '.' . (new Role)->getForeignKey(), '=', (new Role)->getTable() . '.' . (new Role)->getKeyName())
                        ->join((new Privilege)->getTable(), (new PrivilegeValue)->getTable() . '.' . (new Privilege)->getForeignKey(), '=', (new Privilege)->getTable() . '.' . (new Privilege)->getKeyName())
                        ->where((new Role)->getTable() . '.name', '=', $validateInput['roleName'])
                        ->get()
                        ->map(function ($v, $k) use (&$privileges) {
                            $privileges[$v->name] = $v->privilegeValue;
                        });

                    return response()->json($privileges);
                })->name('privileges.show');

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
                Route::get('/order/laser/page', fn () => view('app'))->name('order.laser.page');

                Route::get('/orders/Laser/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name('orders.laserIndex');
                Route::get('/orders/Regular/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name('orders.regularIndex');

                Route::post('/order', 'store')->name('orders.store');

                Route::get('/orders/{businessName}/{accountId}/{orderId}', 'show')->name('orders.show');

                Route::delete('/orders/{businessName}/{accountId}/{orderId}', 'destroy')->name('orders.destroy');

                Route::get('/laser/parts/{gender?}', function (Request $request) {
                    $gender = $request->get('gender', null);
                    if (is_string($gender)) {
                        return Part::query()->where('gender', '=', ucfirst(strtolower($gender)))->get()->toArray();
                    } else {
                        return Part::query()->get()->toArray();
                    }
                })->name('orders.laser.parts');

                Route::get('/laser/packages/{gender?}', function (Request $request) {
                    $gender = $request->get('gender', null);
                    if (is_string($gender)) {
                        return Package::query()->where('gender', '=', ucfirst(strtolower($gender)))->with('parts')->get()->toArray();
                    } else {
                        return Package::query()->with('parts')->get()->toArray();
                    }
                })->name('orders.laser.packages');

                Route::post('/laser/time-calculation', 'calculateTime')->name('timeCalculation');
                Route::post('/laser/price-calculation', 'calculatePrice')->name('priceCalculation');
            });

        Route::controller(VisitsController::class)
            ->group(function () {
                Route::get('/visit/laser/page', fn () => view('app'))->name('visit.laser.page');
                Route::get('/visit/regular/page', fn () => view('app'))->name('visit.regular.page');

                Route::get('/visits/laser/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}', 'laserIndex')->name('visits.laserIndex');
                Route::get('/visits/regular/{accountId?}/{sortByTimestamp?}/{regularOrderId?}/{timestamp?}/{operator?}', 'regularIndex')->name('visits.regularIndex');

                Route::middleware('adjustWeekDaysPeriods')->post('/visit/laser', 'laserStore')->name('visits.laserStore');
                Route::middleware('adjustWeekDaysPeriods')->post('/visit/regular', 'regularStore')->name('visits.regularStore');

                Route::get('/visit/laser/{timestamp}', 'laserShow')->name('visits.laserShow');
                Route::get('/visit/regular/{timestamp}', 'regularShow')->name('visits.regularShow');

                Route::post('/visit/laser/check', 'laserShowAvailable')->name('visits.laserShowAvailable');
                Route::post('/visit/regular/check', 'regularShowAvailable')->name('visits.regularShowAvailable');

                Route::delete('/visit/laser/{laserVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
                Route::delete('/visit/regular/{regularVisitId}/{targetUserId}', 'laserDestroy')->name('visits.laserDestroy');
            });
    });
});
