<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseRulesSeeder extends Seeder
{
    public function run(): void
    {
        Rule::factory()->name('admin')->create();
        Rule::factory()->name('doctor')->create();
        Rule::factory()->name('secretary')->create();
        Rule::factory()->name('operator')->create();
        Rule::factory()->name('patient')->create();
        Rule::factory()->name('custom')->create();
    }
}
