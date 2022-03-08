<?php

namespace Database\Seeders;

use App\Models\Privilege;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use TheClinicDataStructures\DataStructures\User\DSUser;

class DatabasePrivilegesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DSUser::getPrivileges() as $privilege) {
            Privilege::factory()->name($privilege)->create();
        }
    }
}
