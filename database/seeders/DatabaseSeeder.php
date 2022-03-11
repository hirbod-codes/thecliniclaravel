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

            (new DatabasePrivilegesSeeder)->run();
            (new DatabaseRulesSeeder)->run();
            (new DatabasePrivilegeValueSeeder)->run();
            (new DatabaseUsersSeeder)->run();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);
        }
    }
}
