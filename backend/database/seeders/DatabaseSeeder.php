<?php

namespace Database\Seeders;

use App\Models\Auth\Admin;
use App\Models\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        (new DatabaseUserColumnSeeder)->run();
        (new DatabaseBusinessDefaultSeeder)->run();

        (new DatabasePrivilegeNameSeeder)->run();
        (new DatabaseRoleSeeder)->run();

        (new DatabasePartsSeeder)->run();
        (new DatabasePackagesSeeder)->run();

        if (in_array(strtolower(config('app.env')), ['production', 'prod'])) {
            $user = User::factory()
                ->state([
                    'username' => 'hirbod',
                    'email' => 'hirbod.khatami@gmail.com',
                    'phonenumber' => '09380978577',
                ])
                ->create();

            Admin::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'admin')->firstOrFail()->childRoleModel->getKey())
                ->create();

            return;
        }

        (new DatabaseUsersSeeder)->run();

        (new DatabaseOrdersSeeder)->run();

        (new DatabaseVisitsSeeder)->run();
    }
}
