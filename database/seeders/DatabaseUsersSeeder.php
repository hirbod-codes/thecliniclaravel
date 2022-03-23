<?php

namespace Database\Seeders;

use App\Models\roles\AdminRole;
use App\Models\roles\DoctorRole;
use App\Models\roles\OperatorRole;
use App\Models\roles\PatientRole;
use App\Models\roles\SecretaryRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseUsersSeeder extends Seeder
{
    public function run(): void
    {
        AdminRole::factory()->count(3)->create();
        DoctorRole::factory()->count(2)->create();
        SecretaryRole::factory()->count(4)->create();
        OperatorRole::factory()->count(5)->create();
        PatientRole::factory()->count(30)->create();
    }
}
