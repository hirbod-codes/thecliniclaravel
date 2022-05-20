<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseRulesSeeder extends Seeder
{
    public function run(): void
    {
        Role::factory()->name('admin')->role('admin')->create();
        Role::factory()->name('doctor')->role('doctor')->create();
        Role::factory()->name('secretary')->role('secretary')->create();
        Role::factory()->name('operator')->role('operator')->create();
        Role::factory()->name('patient')->role('patient')->create();
    }
}
