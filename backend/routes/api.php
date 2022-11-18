<?php

use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use App\Models\BusinessDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Routes\CommonRoutes;

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
        return response()->json(BusinessDefault::firstOrFail()->genders);
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
        return response()->json((BusinessDefault::query()->firstOrFail()->work_schedule)->toArray());
    })->name('workSchedule');

    Route::middleware('guest:api')->group(function () {
        Route::post('/login', [AuthController::class, 'apiLogin'])->name('auth.apiLogin');
    });

    Route::middleware(['auth:api', 'phonenumber_verified'])->group(function () {
        Route::get('/logout', [AuthController::class, 'apiLogout'])->name('auth.apiLogout');

        (new CommonRoutes)->callCommonRoutes('api');
    });
});
