<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseRulesSeeder extends Seeder
{
    public function run(): void
    {
        Role::factory()->name('admin')->create();
        Role::factory()->name('doctor')->create();
        Role::factory()->name('secretary')->create();
        Role::factory()->name('operator')->create();
        Role::factory()->name('patient')->create();
    }
}
