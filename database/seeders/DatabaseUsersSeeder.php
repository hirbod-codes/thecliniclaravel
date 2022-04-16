<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\roles\DoctorRole;
use App\Models\roles\OperatorRole;
use App\Models\roles\PatientRole;
use App\Models\roles\SecretaryRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseUsersSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser('admin');

            AdminRole::factory()
                ->usersForeignKey($user->{(new User)->getKeyName()})
                ->usersRoleNameForeignKey($user->{(new Role)->getForeignKeyForName()})
                ->create();
        }

        for ($i = 0; $i < 10; $i++) {
            $user = $this->createUser('doctor');

            DoctorRole::factory()
                ->usersForeignKey($user->{(new User)->getKeyName()})
                ->usersRoleNameForeignKey($user->{(new Role)->getForeignKeyForName()})
                ->create();
        }

        for ($i = 0; $i < 15; $i++) {
            $user = $this->createUser('secretary');

            SecretaryRole::factory()
                ->usersForeignKey($user->{(new User)->getKeyName()})
                ->usersRoleNameForeignKey($user->{(new Role)->getForeignKeyForName()})
                ->create();
        }

        for ($i = 0; $i < 20; $i++) {
            $user = $this->createUser('operator');

            OperatorRole::factory()
                ->usersForeignKey($user->{(new User)->getKeyName()})
                ->usersRoleNameForeignKey($user->{(new Role)->getForeignKeyForName()})
                ->create();
        }

        for ($i = 0; $i < 40; $i++) {
            $user = $this->createUser('patient');

            PatientRole::factory()
                ->usersForeignKey($user->{(new User)->getKeyName()})
                ->usersRoleNameForeignKey($user->{(new Role)->getForeignKeyForName()})
                ->create();
        }
    }

    private function createUser(string $roleName): User
    {
        return User::factory()
            ->usersRolesForeignKey($roleName)
            ->create();
    }
}
