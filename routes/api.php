<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['web', 'auth']], function ($router) {
    $router->get('/clients', function () {
    });

    $router->post('/clients', function () {
    });

    $router->put('/clients/{client_id}', function () {
    });

    $router->delete('/clients/{client_id}', function () {
    });
});
