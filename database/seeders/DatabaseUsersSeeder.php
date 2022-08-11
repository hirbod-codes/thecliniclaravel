<?php

namespace Database\Seeders;

use App\Models\Auth\Admin;
use App\Models\Auth\Doctor;
use App\Models\Auth\Operator;
use App\Models\Auth\Patient;
use App\Models\Auth\Secretary;
use App\Models\RoleName;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DatabaseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

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

        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser();

            Admin::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'admin')->firstOrFail()->childRoleModel->getKey())
                ->create();
        }

        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser();

            Doctor::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'doctor')->firstOrFail()->childRoleModel->getKey())
                ->create();
        }

        for ($i = 0; $i < 15; $i++) {
            $user = $this->createUser();

            Secretary::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'secretary')->firstOrFail()->childRoleModel->getKey())
                ->create();
        }

        for ($i = 0; $i < 20; $i++) {
            $user = $this->createUser();

            Operator::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'operator')->firstOrFail()->childRoleModel->getKey())
                ->create();
        }

        for ($i = 0; $i < 40; $i++) {
            $user = $this->createUser();

            if ($i % 2 === 0) {
                Patient::factory()
                    ->userFK($user->getKey())
                    ->roleFK(RoleName::query()->where('name', '=', 'patient')->firstOrFail()->childRoleModel->getKey())
                    ->operatorFK(Operator::query()->whereKey($faker->randomElement(Operator::query()->get([(new Operator)->getKeyName()])))->firstOrFail()->getKey())
                    ->create();

                continue;
            }

            Patient::factory()
                ->userFK($user->getKey())
                ->roleFK(RoleName::query()->where('name', '=', 'patient')->firstOrFail()->childRoleModel->getKey())
                ->create();
        }
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }
}
