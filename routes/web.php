<?php

use App\Http\Controllers\AuthController;
use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

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

Route::get('/locale', function () {
    $locale = App::getLocale();
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
            App::setLocale($dir);
        }
    }

    return response()->json(['message' => 'The locale option successfully updated.']);
});

Route::get('/', function () {
    return view('app');
})->name('home');

Route::middleware('auth:web')->group(function () {
    // Verify Email
    Route::get('/email/verify/{redirecturl?}', function (Request $request) {
        $redirecturl = $request->get('redirecturl');
        $request->session()->put('redirecturl', $redirecturl);
        return view('app');
    })->name('verification.notice');

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
});

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

    // Reset Password
    Route::get('/forgot-password/{redirecturl?}', function (Request $request) {
        $redirecturl = $request->get('redirecturl');
        $request->session()->put('redirecturl', $redirecturl);
        return view('app');
    })->name('forgot_password.page');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot_password');

    Route::put('/reset-password', [AuthController::class, 'resetPassword'])->name('reset_password');
});
