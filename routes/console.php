<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\ConsoleTest;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('emptyThenMigrate', function () {
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    foreach (DB::select('SHOW TABLES') as $table) {
        Schema::dropIfExists($table->{'Tables_in_' . env('DB_DATABASE')});
    }
    DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    $this->comment("Database tables dropped successfully.");

    Artisan::call('migrate');

    $this->comment("Database tables migrated successfully.");
})->purpose('Drop all the tables in your app env(DB_DATABASE).');

Artisan::command('initialize', function () {
    if (env('APP_ENV') !== 'local') {
        $this->comment("Application is not in local environment.");
        return;
    }

    Artisan::call('emptyThenMigrate');
    $this->comment(Artisan::output());

    Artisan::call('passport:install');
    $this->comment(Artisan::output());

    // Add personal access client and password grant client id and secret to .env file
    $output = Artisan::output();
    $envStr = file_get_contents(__DIR__ . '/../.env');
    $envStr = explode('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $envStr)[0];
    $personalAccessPassed = false;
    $result = '';
    for ($i = 0; $i < count($outputSegments = explode("\n", $output)); $i++) {
        if (!$personalAccessPassed && strpos($outputSegments[$i], 'Client ID:') !== false) {
            $personalAccessPassed = true;
            $clientId = str_replace('Client ID: ', '', $outputSegments[$i]);
            $i++;
            $clientSecret = str_replace('Client secret: ', '', $outputSegments[$i]);
            $result .= "PASSPORT_PERSONAL_ACCESS_CLIENT_ID=\"" . strval(intval($clientId)) . "\"\nPASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=\"" . $clientSecret . "\"";
        }

        if ($personalAccessPassed && strpos($outputSegments[$i], 'Client ID:') !== false) {
            $personalAccessPassed = true;
            $clientId = str_replace('Client ID: ', '', $outputSegments[$i]);
            $i++;
            $clientSecret = str_replace('Client secret: ', '', $outputSegments[$i]);
            $result .= "\n\nPASSPORT_PASSWORD_GRANT_CLIENT_ID=\"" . strval(intval($clientId)) . "\"\nPASSPORT_PASSWORD_GRANT_CLIENT_SECRET=\"" . $clientSecret . "\"";

            break;
        }
    }
    file_put_contents(__DIR__ . '/../.env', $envStr . $result);

    Artisan::call('passport:keys');
    $this->comment(Artisan::output());

    Artisan::call('db:seed');
    $this->comment(Artisan::output());

    $this->comment("\n\nApplication initializing finished.");
});
