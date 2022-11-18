<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Routes\CommonRoutes;

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
    Route::get('/register/{redirecturl?}', function (Request $request) {
        if (!is_null($redirecturl = $request->get('redirecturl'))) {
            $request->session()->put('redirecturl', $redirecturl);
        }
        return view('app');
    })->name('auth.register.page');

    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    Route::get('/login', fn () => view('app'))->name('auth.login.page');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::get('/auth/phonenumber-availability/{phonenumber?}', [AuthController::class, 'phonenumberAvailability'])->name('auth.phonenumberAvailability');

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

    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::put('/auth/update-phonenumber', [AuthController::class, 'updatePhonenumber'])->name('auth.updatePhonenumber');

    (new CommonRoutes)->callCommonRoutes('web');
});
