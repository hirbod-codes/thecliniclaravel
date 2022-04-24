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
    return view('auth.forgot-password');
    return 200;
})->name('home');

Route::middleware('auth:web')->group(function () {
    // Verify Email
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/home');
    })->middleware('signed')->name('verification.verify');

    // Reset Password
    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('forgot_password.page');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot_password');

    Route::get('/reset-password', fn () => view('auth.reset-password'))->name('reset_password.pqge');
    Route::put('/reset-password', [AuthController::class, 'resetPassword'])->name('reset_password');

    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// Register
Route::middleware('guest:web')->get('/register', fn () => view('auth.register'))->name('auth.register.page');
Route::middleware('guest:web')->post('/register', [AuthController::class, 'register'])->name('auth.register');

// Login
Route::middleware('guest:web')->get('/login', fn () => view('auth.login'))->name('auth.login.page');
Route::middleware('guest:web')->post('/login', [AuthController::class, 'login'])->name('auth.login');

// Verify Phonenumber
Route::middleware('phonenumber_not_verified')->get('/register/verifyPhonenumber', fn () => view('auth.verify-phonenumber'))->name('auth.verifyPhonenumber.page');
Route::middleware('phonenumber_not_verified')->post('/register/verifyPhonenumber', [AuthController::class, 'verifyPhonenumber'])->name('auth.verifyPhonenumber');
