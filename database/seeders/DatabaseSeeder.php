<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            (new DatabaseBusinessDefaultSeeder)->run();

            (new DatabasePrivilegesSeeder)->run();
            (new DatabaseRulesSeeder)->run();
            (new DatabasePrivilegeValueSeeder)->run();

            (new DatabasePartsSeeder)->run();
            (new DatabasePackagesSeeder)->run();

            if (in_array(strtolower(env('APP_ENV', '')), ['production', 'prod'])) {
                DB::commit();
                return;
            }

            (new DatabaseUsersSeeder)->run();

            (new DatabaseOrdersSeeder)->run();

            (new DatabaseVisitsSeeder)->run();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
