<?php

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

Artisan::command('consoleTest', function () {
    (new ConsoleTest)->runTests();
    $this->info('Console test has finished.');
});

Artisan::command('initialize', function () {
    $t = explode(' ', microtime());
    $ms = $t[0];
    $s = $t[1];

    if (in_array(config('app.env'), ['prod', 'production']) && count(array_map(function ($t) {
        foreach ($t as $key => $v) {
            return $v;
        }
    }, DB::select('SHOW TABLES'))) !== 0) {
        $this->info("Application already initialized.");
        return;
    }

    $this->call('emptyThenMigrate');

    $this->call('installPassport');

    $this->call('dbSeed');

    $this->newLine();
    $this->newLine();
    $this->info("Application initializing has finished.");

    $t = explode(' ', microtime());
    $ms1 = $t[0];
    $s1 = $t[1];
    $this->info("Total duration: " . strval(($s1 - $s) - ($ms1 - $ms)));
});

Artisan::command('emptyThenMigrate', function () {
    $t = explode(' ', microtime());
    $ms = $t[0];
    $s = $t[1];

    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    foreach (DB::select('SHOW TABLES') as $table) {
        Schema::dropIfExists($table->{'Tables_in_' . config('database.connections.mysql.database')});
    }
    DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    $this->info("Database tables dropped successfully.");

    $this->call('migrate', ['--verbose' => true, '--force' => true, '--no-interaction' => true]);

    $t = explode(' ', microtime());
    $ms1 = $t[0];
    $s1 = $t[1];
    $this->newLine();
    $this->info("The command emptyThenMigrate duration: " . strval(($s1 - $s) - ($ms1 - $ms)));
    $this->newLine();
    $this->newLine();
})->purpose('Drop all the tables in your app config(\'database.connections.mysql.database\'), Then run: php artisan migrate.');

Artisan::command('installPassport', function () {
    $t = explode(' ', microtime());
    $ms = $t[0];
    $s = $t[1];

    Artisan::call('passport:install');
    $this->info($output = Artisan::output());

    // Add personal access client and password grant client id and secret to .env file
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

    $this->call('passport:keys');

    $this->info("Passport package installation has finished.");
    $t = explode(' ', microtime());
    $ms1 = $t[0];
    $s1 = $t[1];
    $this->newLine();
    $this->info("The command installPassport duration: " . strval(($s1 - $s) - ($ms1 - $ms)));
    $this->newLine();
    $this->newLine();
});

Artisan::command('dbSeed', function () {
    $t = explode(' ', microtime());
    $ms = $t[0];
    $s = $t[1];

    $this->call('db:seed', ['--verbose' => true, '--force' => true, '--no-interaction' => true]);

    $t = explode(' ', microtime());
    $ms1 = $t[0];
    $s1 = $t[1];

    $this->newLine();
    $this->info("The command db:seed duration: " . strval(($s1 - $s) - ($ms1 - $ms)));
    $this->newLine();
    $this->newLine();
});
