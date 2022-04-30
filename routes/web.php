<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
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

Route::get('/', function () {
    return 200;
})->name('home');

Route::middleware('auth:web')->group(function () {
    // Verify Email
    Route::get('/email/verify/{redirecturl?}', function (Request $request) {
        $redirecturl = $request->get('redirecturl');
        $request->session()->put('redirecturl', $redirecturl);
        return view('auth.verify-email');
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

        return view('auth.verified-email');
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
        return view('auth.register');
    })->name('auth.register.page');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    // Login
    Route::get('/login', fn () => view('auth.login'))->name('auth.login.page');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Verify Phonenumber
    Route::middleware('phonenumber_not_verified')->post('/register/send-phoennumber-verification-code', [AuthController::class, 'sendPhonenumberVerificationCode'])->name('auth.sendPhonenumberVerificationCode');
    Route::middleware('phonenumber_not_verified')->post('/register/verify-phoennumber-verification-code', [AuthController::class, 'verifyPhonenumberVerificationCode'])->name('auth.verifyPhonenumberVerificationCode');

    // Reset Password
    Route::get('/forgot-password/{redirecturl?}', function (Request $request) {
        $redirecturl = $request->get('redirecturl');
        $request->session()->put('redirecturl', $redirecturl);
        return view('auth.forgot-password');
    })->name('forgot_password.page');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot_password');

    Route::put('/reset-password', [AuthController::class, 'resetPassword'])->name('reset_password');
});
