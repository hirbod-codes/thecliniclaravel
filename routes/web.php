<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AccountDocumentsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessDefault;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\UpdateLocaleRequest;
use App\Models\Package\Package;
use App\Models\Part\Part;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Models\BusinessDefault as ModelsBusinessDefault;

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
});

Route::get('/auth/phonenumber-availability/{phonenumber?}', [AuthController::class, 'phonenumberAvailability'])->name('auth.phonenumberAvailability');

// Verify Phonenumber
Route::post('/auth/verify-phonenumber', [AuthController::class, 'verifyPhonenumber'])->name('auth.verifyPhonenumber');

Route::post('/auth/send-code-to-phonenumber', [AuthController::class, 'sendCodeToPhonenumber'])->name('auth.sendCodeToPhonenumber');
Route::post('/auth/send-code-to-email', [AuthController::class, 'sendCodeToEmail'])->name('auth.sendCodeToEmail');

Route::put('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.resetPassword');

Route::middleware(['auth:web', 'phonenumber_verified'])->group(function () {
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

    Route::put('/auth/update-phonenumber', [AuthController::class, 'updatePhonenumber'])->name('auth.updatePhonenumber');

    Route::get('/work-schedule', function () {
        return response()->json(($t = ModelsBusinessDefault::query()->firstOrFail()->work_schedule)->toArray());
    })->name('workSchedule');

    Route::controller(AccountsController::class)
        ->group(function () {
            Route::get('/dashboard/account', fn () => view('app'))->name('account.page');

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
            Route::get('/dashboard/order', fn () => view('app'))->name('order.laser.page');

            Route::get('/orders/laser/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'laserIndex')->name('orders.laserIndex');
            Route::get('/orders/regular/{roleName?}/{priceOtherwiseTime?}/{username?}/{lastOrderId?}/{count?}/{operator?}/{price?}/{timeConsumption?}', 'regularIndex')->name('orders.regularIndex');
            Route::get('/ordersCount/{businessName?}/{roleName?}', 'ordersCount')->name('orders.ordersCount');

            Route::post('/order', 'store')->name('orders.store');

            Route::delete('/order', 'destroy')->name('orders.destroy');

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
            Route::get('/dashboard/visit', fn () => view('app'))->name('visit.laser.page');

            Route::get('/visits/{businessName?}/{roleName?}/{accountId?}/{sortByTimestamp?}/{laserOrderId?}/{timestamp?}/{operator?}/{count?}/{lastVisitTimestamp?}', 'index')->name('visits.index');
            Route::get('/visitsCount/{businessName?}/{roleName?}', 'visitsCount')->name('visits.visitsCount');

            Route::middleware('adjustWeekDaysPeriods')->post('/visit/laser', 'laserStore')->name('visits.laserStore');
            Route::middleware('adjustWeekDaysPeriods')->post('/visit/regular', 'regularStore')->name('visits.regularStore');

            Route::post('/visit/laser/check', 'laserShowAvailable')->name('visits.laserShowAvailable');
            Route::post('/visit/regular/check', 'regularShowAvailable')->name('visits.regularShowAvailable');

            Route::delete('/visit/laser/{laserVisitId}', 'laserDestroy')->name('visits.laserDestroy');
            Route::delete('/visit/regular/{regularVisitId}', 'regularDestroy')->name('visits.regularDestroy');
        });
});
