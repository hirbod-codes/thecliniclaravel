<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseRulesSeeder extends Seeder
{
    public function run(): void
    {
        Rule::factory()->name('admin')->craete();
        Rule::factory()->name('doctor')->craete();
        Rule::factory()->name('secretary')->craete();
        Rule::factory()->name('operator')->craete();
        Rule::factory()->name('patient')->craete();
        Rule::factory()->name('custom')->craete();
    }
}
